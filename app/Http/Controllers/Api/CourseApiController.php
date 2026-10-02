<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\CourseResource;
use App\Http\Resources\MaterialResource;
use App\Models\Course;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class CourseApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $courses = match ($user->role) {
            'admin' => Course::query(),
            'dosen' => $user->taughtCourses(),
            'mahasiswa' => $user->courses(),
            default => abort(403),
        };

        $courses = $courses
            ->with('lecturer')
            ->withCount(['materials', 'assignments'])
            ->orderBy('name')
            ->paginate(15);

        return ApiResponse::collection($courses, CourseResource::class, $request);
    }

    public function show(Request $request, int $id)
    {
        $course = Course::with('lecturer')
            ->withCount(['materials', 'assignments'])
            ->findOrFail($id);

        $this->authorizeCourseAccess($request, $course);

        return new CourseResource($course);
    }

    public function materials(Request $request, int $id)
    {
        $course = Course::findOrFail($id);
        $this->authorizeCourseAccess($request, $course);

        $materials = $course->materials()
            ->with('uploader')
            ->latest()
            ->paginate(15);

        return ApiResponse::collection($materials, MaterialResource::class, $request);
    }

    public function assignments(Request $request, int $id)
    {
        $course = Course::findOrFail($id);
        $this->authorizeCourseAccess($request, $course);

        $validated = $request->validate([
            'status' => ['sometimes', 'in:draft,published'],
        ]);

        $assignments = $course->assignments()
            ->when($request->user()->role === 'mahasiswa', fn ($query) => $query->where('status', 'published'))
            ->when(isset($validated['status']), fn ($query) => $query->where('status', $validated['status']))
            ->orderByDesc('due_at')
            ->paginate(15);

        return ApiResponse::collection($assignments, AssignmentResource::class, $request);
    }

    private function authorizeCourseAccess(Request $request, Course $course): void
    {
        $user = $request->user();

        $allowed = match ($user->role) {
            'admin' => true,
            'dosen' => $course->lecturer_id === $user->id,
            'mahasiswa' => $course->students()->whereKey($user->id)->exists(),
            default => false,
        };

        abort_unless($allowed, 403);
    }
}