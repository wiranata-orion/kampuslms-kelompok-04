<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGradeRequest;
use App\Http\Requests\UpdateGradeRequest;
use App\Http\Requests\UpdateSubmissionRequest;
use App\Http\Resources\GradeResource;
use App\Http\Resources\SubmissionResource;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\SubmissionAccessLog;
use App\Support\ApiResponse;
use App\Support\PrivateFiles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class SubmissionController extends Controller
{
    public function mine(Request $request)
    {
        Gate::authorize('viewAny', Submission::class);

        $submissions = $request->user()->submissions()
            ->with(['assignment.course', 'grade'])
            ->latest('submitted_at')
            ->paginate(15);

        return ApiResponse::collection($submissions, SubmissionResource::class, $request);
    }

    public function show(Request $request, Submission $submission): SubmissionResource
    {
        $submission->load(['assignment.course', 'student', 'grade.grader']);
        Gate::authorize('view', $submission);

        return new SubmissionResource($submission);
    }

    public function showGrade(Request $request, Grade $grade): SubmissionResource
    {
        $grade->load(['submission.assignment.course', 'submission.student', 'grader']);
        Gate::authorize('view', $grade);

        $grade->submission->setRelation('grade', $grade);

        return new SubmissionResource($grade->submission);
    }

    public function download(Request $request, Submission $submission)
    {
        $submission->loadMissing('assignment.course');
        Gate::authorize('download', $submission);

        abort_unless(filled($submission->file_path), 404, 'Berkas submission tidak tersedia.');

        $preview = $request->boolean('preview') && PrivateFiles::canPreview($submission->file_path);

        // Dibuat dulu: abort 404 terjadi di sini, sebelum audit dicatat.
        $response = PrivateFiles::response($submission->file_path, $submission->original_name, $preview);

        // Audit: akses oleh selain pemilik (dosen pengampu atau admin).
        if ($request->user()->id !== $submission->user_id) {
            SubmissionAccessLog::create([
                'submission_id' => $submission->id,
                'user_id' => $request->user()->id,
                'action' => $preview ? 'preview' : 'download',
                'original_name' => $submission->original_name,
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
            ]);
        }

        return $response;
    }

    public function studentGrade(Request $request, Submission $submission): GradeResource
    {
        $submission->load(['assignment.course', 'grade.grader']);
        abort_unless($submission->grade, 404);
        Gate::authorize('view', $submission->grade);

        return new GradeResource($submission->grade);
    }

    public function grade(StoreGradeRequest $request, Submission $submission): JsonResponse
    {
        $submission->load(['assignment.course', 'grade']);
        $grade = $submission->grade;

        $validated = $request->validated();

        $grade ??= new Grade(['submission_id' => $submission->id]);
        $grade->graded_by = $request->user()->id;
        $grade->score = $validated['score'];
        $grade->feedback = $validated['feedback'] ?? null;
        $grade->graded_at = now();
        $grade->published_at = null;
        $grade->save();

        return (new GradeResource($grade->load('grader')))
            ->response()
            ->setStatusCode($grade->wasRecentlyCreated ? 201 : 200);
    }

    public function publishGrade(Request $request, Grade $grade): GradeResource
    {
        $grade->load('submission.assignment.course');
        Gate::authorize('publish', $grade);
        $grade->published_at = now();
        $grade->save();

        return new GradeResource($grade->load('grader'));
    }

    public function updateGrade(UpdateGradeRequest $request, Grade $grade): JsonResponse
    {
        $grade->load(['submission.assignment.course']);
        Gate::authorize('update', $grade);

        $validated = $request->validated();
        $grade->score = $validated['score'];
        $grade->feedback = $validated['feedback'] ?? null;
        $grade->graded_by = $request->user()->id;
        $grade->graded_at = now();
        $grade->save();

        return (new GradeResource($grade->load('grader')))->response();
    }
}
