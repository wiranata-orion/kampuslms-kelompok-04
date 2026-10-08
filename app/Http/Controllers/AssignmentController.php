<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssignmentResource;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class AssignmentController extends Controller
{
    public function index(Request $request, Course $course): AnonymousResourceCollection
    {
        $this->authorizeLecturerOrAdmin($request, $course);

        $assignments = $course->assignments()->latest('due_at')->paginate(15);

        return AssignmentResource::collection($assignments);
    }

    public function show(Request $request, Assignment $assignment): AssignmentResource
    {
        $this->authorizeLecturerOrAdmin($request, $assignment->course);

        $assignment->load('course');

        return new AssignmentResource($assignment);
    }

    public function store(Request $request, Course $course): JsonResponse
    {
        $this->authorizeLecturer($request, $course);

        $validated = $this->validatedInput($request);

        $assignment = new Assignment();
        $assignment->course_id = $course->id;
        $assignment->created_by = $request->user()->id;
        $assignment->title = $validated['title'];
        $assignment->instructions = $validated['instructions'];
        $assignment->due_at = $validated['due_at'];
        $assignment->max_score = $validated['max_score'] ?? 100;
        $assignment->allow_late = $request->boolean('allow_late');
        $assignment->status = $validated['status'];
        $assignment->save();

        return (new AssignmentResource($assignment))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Assignment $assignment): AssignmentResource
    {
        $this->authorizeLecturer($request, $assignment->course);

        $validated = $this->validatedInput($request);

        $assignment->title = $validated['title'];
        $assignment->instructions = $validated['instructions'];
        $assignment->due_at = $validated['due_at'];
        $assignment->max_score = $validated['max_score'] ?? 100;
        $assignment->allow_late = $request->boolean('allow_late');
        $assignment->status = $validated['status'];
        $assignment->save();

        return new AssignmentResource($assignment);
    }

    public function destroy(Request $request, Assignment $assignment): JsonResponse
    {
        $this->authorizeLecturer($request, $assignment->course);

        $assignment->delete();

        return response()->json(['message' => 'Tugas berhasil dihapus.'], 200);
    }

    private function authorizeLecturer(Request $request, Course $course): void
    {
        abort_unless(
            $request->user() && $course->lecturer_id === $request->user()->id,
            403,
            'Kamu bukan dosen pengampu mata kuliah ini.'
        );
    }

    private function authorizeLecturerOrAdmin(Request $request, Course $course): void
    {
        $user = $request->user();
        abort_unless(
            $user && ($course->lecturer_id === $user->id || $user->role === 'admin'),
            403,
            'Kamu tidak memiliki akses ke tugas mata kuliah ini.'
        );
    }

    private function validatedInput(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'instructions' => ['required', 'string'],
            'due_at' => ['required', 'date'],
            'max_score' => ['nullable', 'integer', 'between:1,100'],
            'allow_late' => ['nullable', 'boolean'],
            'status' => ['required', 'in:draft,published'],
        ]);
    }
}