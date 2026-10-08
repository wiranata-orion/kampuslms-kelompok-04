<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GradeResource;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
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
