<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGradeRequest;
use App\Http\Resources\GradeResource;
use App\Http\Resources\SubmissionResource;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\SubmissionAccessLog;
use App\Notifications\GradePublishedNotification;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function updateMine(Request $request, Submission $submission): never
    {
        Gate::authorize('update', $submission);
        abort(403);
    }

    public function download(Request $request, Submission $submission)
    {
        $submission->load('assignment.course');
        Gate::authorize('view', $submission);

        return Storage::disk('local')->download($submission->file_path, $submission->original_name);
    }

    public function studentGrade(Request $request, Submission $submission): GradeResource
    {
        $submission->load(['assignment.course', 'grade.grader']);
        abort_unless($submission->grade, 404);
        Gate::authorize('view', $submission->grade);

        return new GradeResource($submission->grade);
    }

    public function grade(Request $request, Submission $submission): JsonResponse
    {
        $submission->load(['assignment.course', 'grade']);

        $validated = $request->validated();
        $grade = Grade::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'graded_by' => $request->user()->id,
                'score' => $validated['score'],
                'feedback' => $validated['feedback'] ?? null,
                'graded_at' => now(),
            ],
        );
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

        $grade = DB::transaction(function () use ($grade) {
            $grade = Grade::query()
                ->whereKey($grade->getKey())
                ->lockForUpdate()
                ->with(['submission.student', 'submission.assignment.course'])
                ->firstOrFail();

            if ($grade->published_at === null) {
                $grade->published_at = now();
                $grade->save();
                $grade->submission->student->notify(new GradePublishedNotification($grade));
            }

            return $grade;
        });

        return new GradeResource($grade->load('grader'));
    }

    // @php-cs-fixer-ignore fully_qualified_strict_types
    public function updateGrade(\App\Http\Requests\UpdateGradeRequest $request, Grade $grade): JsonResponse
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
