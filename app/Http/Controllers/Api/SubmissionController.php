<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GradeResource;
use App\Http\Resources\SubmissionResource;
use App\Models\Grade;
use App\Models\Submission;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

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

    public function show(Request $request, int $id): SubmissionResource
    {
        $submission = Submission::with(['assignment.course', 'student', 'grade.grader'])->findOrFail($id);
        Gate::authorize('view', $submission);

        return new SubmissionResource($submission);
    }

    public function showGrade(Request $request, int $id): SubmissionResource
    {
        $grade = Grade::with(['submission.assignment.course', 'submission.student', 'grader'])
            ->findOrFail($id);
        Gate::authorize('view', $grade);

        $grade->submission->setRelation('grade', $grade);

        return new SubmissionResource($grade->submission);
    }

    public function updateMine(Request $request, int $id): never
    {
        $submission = Submission::findOrFail($id);
        Gate::authorize('update', $submission);
        abort(403);
    }

    public function download(Request $request, int $id)
    {
        $submission = Submission::with('assignment.course')->findOrFail($id);
        Gate::authorize('view', $submission);

        return Storage::disk('local')->download($submission->file_path, $submission->original_name);
    }

    public function studentGrade(Request $request, int $id): GradeResource
    {
        $submission = Submission::with(['assignment.course', 'grade.grader'])->findOrFail($id);
        abort_unless($submission->grade, 404);
        Gate::authorize('view', $submission->grade);

        return new GradeResource($submission->grade);
    }

    public function grade(Request $request, int $id): JsonResponse
    {
        $submission = Submission::with(['assignment.course', 'grade'])->findOrFail($id);
        $grade = $submission->grade;
        Gate::authorize($grade ? 'update' : 'create', $grade ?? [Grade::class, $submission]);

        $validated = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:'.$submission->assignment->max_score],
            'feedback' => ['nullable', 'string'],
        ]);

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

    public function publishGrade(Request $request, int $id): GradeResource
    {
        $grade = Grade::with('submission.assignment.course')->findOrFail($id);
        Gate::authorize('publish', $grade);
        $grade->published_at = now();
        $grade->save();

        return new GradeResource($grade->load('grader'));
    }
}
