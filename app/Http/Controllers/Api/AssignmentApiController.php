<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubmissionRequest;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\SubmissionResource;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssignmentApiController extends Controller
{
    public function show(Request $request, int $id): AssignmentResource
    {
        $assignment = Assignment::with('course')->findOrFail($id);
        Gate::authorize('view', $assignment);

        return new AssignmentResource($assignment);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Assignment::class);

        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:150'],
            'instructions' => ['required', 'string'],
            'due_at' => ['required', 'date'],
            'max_score' => ['nullable', 'integer', 'between:1,100'],
            'allow_late' => ['nullable', 'boolean'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $course = Course::findOrFail($validated['course_id']);
        Gate::authorize('create', [Assignment::class, $course]);

        $assignment = new Assignment;
        $assignment->course_id = $course->id;
        $assignment->created_by = $request->user()->id;
        $this->applyAssignmentInput($assignment, $validated);
        $assignment->save();

        return (new AssignmentResource($assignment))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $id)
    {
        $assignment = Assignment::with('course')->findOrFail($id);
        Gate::authorize('update', $assignment);

        $required = $request->isMethod('put') ? 'required' : 'sometimes';
        $validated = $request->validate([
            'title' => [$required, 'string', 'max:150'],
            'instructions' => [$required, 'string'],
            'due_at' => [$required, 'date'],
            'max_score' => ['sometimes', 'nullable', 'integer', 'between:1,100'],
            'allow_late' => ['sometimes', 'boolean'],
            'status' => [$required, 'in:draft,published'],
        ]);

        $this->applyAssignmentInput($assignment, $validated);
        $assignment->save();

        return new AssignmentResource($assignment);
    }

    public function destroy(Request $request, int $id)
    {
        $assignment = Assignment::with('course')->findOrFail($id);
        Gate::authorize('delete', $assignment);
        $assignment->delete();

        return response()->noContent();
    }

    public function submissions(Request $request, int $id)
    {
        $assignment = Assignment::with('course')->findOrFail($id);
        Gate::authorize('viewAny', [Submission::class, $assignment]);

        $submissions = $assignment->submissions()
            ->with(['student', 'grade.grader'])
            ->latest('submitted_at')
            ->paginate(15);

        return ApiResponse::collection($submissions, SubmissionResource::class, $request);
    }

    public function submit(StoreSubmissionRequest $request, Assignment $assignment)
    {
        $validated = $request->validated();
        $file = $request->file('file');
        $submission = new Submission;
        $submission->assignment_id = $assignment->id;
        $submission->user_id = $request->user()->id;
        $submission->file_path = $file->store('submissions', 'local');
        $submission->original_name = $file->getClientOriginalName();
        $submission->file_size = $file->getSize();
        $submission->note = $validated['note'] ?? null;
        $submission->submitted_at = now();
        $submission->is_late = now()->greaterThan($assignment->due_at);
        $submission->save();

        $submission->load(['student', 'grade.grader']);

        return (new SubmissionResource($submission))
            ->response()->setStatusCode(201);
    }

    private function applyAssignmentInput(Assignment $assignment, array $validated): void
    {
        foreach (['title', 'instructions', 'due_at', 'max_score', 'status'] as $field) {
            if (array_key_exists($field, $validated) && ! ($field === 'max_score' && $validated[$field] === null)) {
                $assignment->{$field} = $validated[$field];
            }
        }

        if (array_key_exists('allow_late', $validated)) {
            $assignment->allow_late = $validated['allow_late'];
        } elseif (! $assignment->exists) {
            $assignment->allow_late = true;
        }

        if (! isset($validated['max_score']) && ! $assignment->exists) {
            $assignment->max_score = 100;
        }
    }
}
