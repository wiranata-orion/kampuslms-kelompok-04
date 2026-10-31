<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\CourseResource;
use App\Http\Resources\MaterialResource;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        Gate::authorize('viewAny', Course::class);

        $scope = $request->query('scope', $request->route('scope', 'my'));
        abort_unless(in_array($scope, ['all', 'my'], true), 422, 'Scope mata kuliah tidak valid.');
        $request->validate(['status' => ['sometimes', 'in:draft,active,archived']]);

        $courses = match ($user->role) {
            'admin' => Course::query(),
            'dosen' => $user->taughtCourses(),
            'mahasiswa' => $user->courses()->where('courses.status', 'active'),
            default => Course::query()->whereRaw('1 = 0'),
        };

        $courses = $courses
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->query('search'));
                $query->where(fn ($q) => $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%"));
            })
            ->with('lecturer')
            ->withCount(['materials', 'assignments'])
            ->orderBy('name')
            ->paginate(15);

        return ApiResponse::collection($courses, CourseResource::class, $request);
    }

    public function show(Request $request, Course $course): CourseResource
    {
        $course->load('lecturer')->loadCount(['materials', 'assignments']);
        Gate::authorize('view', $course);

        return new CourseResource($course);
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        Gate::authorize('create', Course::class);
        $validated = $request->validated();

        $course = new Course;
        $course->code = $validated['code'];
        $course->name = $validated['name'];
        $course->description = $validated['description'] ?? '';
        $course->sks = $validated['sks'];
        $course->lecturer_id = $validated['lecturer_id'];
        $course->status = $validated['status'];
        $course->save();
        $course->load('lecturer')->loadCount(['materials', 'assignments']);

        return response()->json(['data' => (new CourseResource($course))->resolve($request)], 201);
    }

    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        Gate::authorize('update', $course);
        $validated = $request->validated();

        $course->code = $validated['code'];
        $course->name = $validated['name'];
        $course->description = $validated['description'] ?? '';
        $course->sks = $validated['sks'];
        $course->lecturer_id = $validated['lecturer_id'];
        $course->status = $validated['status'];
        $course->save();
        $course->load('lecturer')->loadCount(['materials', 'assignments']);

        return response()->json(['data' => (new CourseResource($course))->resolve($request)]);
    }

    public function destroy(Request $request, Course $course): JsonResponse
    {
        Gate::authorize('delete', $course);
        $course->delete();

        return response()->json(['data' => ['message' => 'Mata kuliah berhasil dihapus.']]);
    }

    public function materials(Request $request, Course $course)
    {
        Gate::authorize('viewAny', [Material::class, $course]);

        $materials = $course->materials()
            ->with(['uploader', 'course'])
            ->latest()
            ->paginate(15);

        return ApiResponse::collection($materials, MaterialResource::class, $request);
    }

    public function assignments(Request $request, Course $course)
    {
        Gate::authorize('viewAny', [Assignment::class, $course]);

        $validated = $request->validate([
            'status' => ['sometimes', 'in:draft,published'],
        ]);

        $assignments = $course->assignments()
            ->with('course')
            ->withCount('grades')
            ->when($request->user()->role === 'mahasiswa', fn ($query) => $query->where('status', 'published'))
            ->when(isset($validated['status']), fn ($query) => $query->where('status', $validated['status']))
            ->orderByDesc('due_at')
            ->paginate(15);

        return ApiResponse::collection($assignments, AssignmentResource::class, $request);
    }
}
