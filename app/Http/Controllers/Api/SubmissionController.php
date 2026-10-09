<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GradeResource;
use App\Http\Resources\SubmissionResource;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Support\ApiResponse;

class SubmissionController extends Controller
{
	public function mine(Request $request)
	{
		abort_unless($request->user()->role === 'mahasiswa', 403);

		$submissions = $request->user()->submissions()
			->with(['assignment.course', 'grade'])
			->latest('submitted_at')
			->paginate(15);

		return ApiResponse::collection($submissions, SubmissionResource::class, $request);
	}

	public function show(Request $request, int $id): SubmissionResource
	{
		$submission = Submission::with(['assignment.course', 'student', 'grade.grader'])->findOrFail($id);
		$user = $request->user();
		$allowed = match ($user->role) {
			'admin' => true,
			'dosen' => $submission->assignment->course->lecturer_id === $user->id,
			'mahasiswa' => $submission->user_id === $user->id,
			default => false,
		};
		abort_unless($allowed, 403);

		return new SubmissionResource($submission);
	}

	public function showGrade(Request $request, int $id): SubmissionResource
	{
		abort_unless($request->user()->role === 'dosen', 403);

		$grade = Grade::with(['submission.assignment.course', 'submission.student', 'grader'])
			->findOrFail($id);
		abort_unless($grade->submission->assignment->course->lecturer_id === $request->user()->id, 403);

		$grade->submission->setRelation('grade', $grade);

		return new SubmissionResource($grade->submission);
	}

	public function updateMine(Request $request, int $id): SubmissionResource
	{
		abort_unless($request->user()->role === 'mahasiswa', 403);
		$submission = Submission::with('assignment')->findOrFail($id);
		abort_unless($submission->user_id === $request->user()->id, 403);
		$assignment = $submission->assignment;

		if (! $assignment->allow_late && now()->greaterThan($assignment->due_at)) {
			abort(422, 'Batas waktu pengumpulan sudah lewat.');
		}

		$validated = $request->validate([
			'file' => ['nullable', 'file', 'max:10240'],
			'note' => ['nullable', 'string'],
		]);

		if ($request->hasFile('file')) {
			Storage::disk('local')->delete($submission->file_path);
			$file = $request->file('file');
			$submission->file_path = $file->store('submissions', 'local');
			$submission->original_name = $file->getClientOriginalName();
			$submission->file_size = $file->getSize();
			$submission->submitted_at = now();
			$submission->is_late = now()->greaterThan($assignment->due_at);
		}
		if (array_key_exists('note', $validated)) {
			$submission->note = $validated['note'];
		}
		$submission->save();
		$submission->load(['assignment.course', 'student', 'grade.grader']);

		return new SubmissionResource($submission);
	}

	public function download(Request $request, int $id)
	{
		$submission = Submission::with('assignment.course')->findOrFail($id);
		$user = $request->user();
		$allowed = match ($user->role) {
			'admin' => true,
			'dosen' => $submission->assignment->course->lecturer_id === $user->id,
			'mahasiswa' => $submission->user_id === $user->id,
			default => false,
		};
		abort_unless($allowed, 403);

		return Storage::disk('local')->download($submission->file_path, $submission->original_name);
	}

	public function studentGrade(Request $request, int $id)
	{
		abort_unless($request->user()->role === 'mahasiswa', 403);
		$submission = Submission::with(['assignment.course', 'grade.grader'])->findOrFail($id);
		abort_unless($submission->user_id === $request->user()->id, 403);
		abort_unless($submission->assignment->course->students()->whereKey($request->user()->id)->exists(), 403);
		abort_unless($submission->grade, 404);

		return new GradeResource($submission->grade);
	}

	public function grade(Request $request, int $id)
	{
		abort_unless($request->user()->role === 'dosen', 403);

		$submission = Submission::with('assignment.course')->findOrFail($id);
		abort_unless($submission->assignment->course->lecturer_id === $request->user()->id, 403);

		$validated = $request->validate([
			'score' => ['required', 'numeric', 'min:0', 'max:'.$submission->assignment->max_score],
			'feedback' => ['nullable', 'string'],
		]);

		$grade = Grade::updateOrCreate(
			['submission_id' => $submission->id],
			[
				'graded_by' => $request->user()->id,
				'score' => $validated['score'],
				'feedback' => $validated['feedback'] ?? null,
				'graded_at' => now(),
			]
		);

		return (new GradeResource($grade->load('grader')))
			->response()
			->setStatusCode($grade->wasRecentlyCreated ? 201 : 200);
	}
}
