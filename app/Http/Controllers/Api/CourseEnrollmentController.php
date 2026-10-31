<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnrollmentRequest;
use App\Http\Resources\CourseResource;
use App\Http\Resources\UserResource;
use App\Models\Course;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseEnrollmentController extends Controller
{
    public function candidates(Request $request, Course $course): JsonResponse
    {
        Gate::authorize('manageEnrollment', $course);
        $students = User::where('role', 'mahasiswa')
            ->whereDoesntHave('courses', fn ($query) => $query->whereKey($course->id))
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => [
                'course' => (new CourseResource($course->load('lecturer')))->resolve($request),
                'candidates' => UserResource::collection($students)->resolve($request),
            ],
        ]);
    }

    public function students(Request $request, Course $course): JsonResponse
    {
        Gate::authorize('manageEnrollment', $course);
        $students = $course->students()->orderBy('name')->paginate(20);

        return ApiResponse::collection($students, UserResource::class, $request);
    }

    public function store(StoreEnrollmentRequest $request, Course $course): JsonResponse
    {
        $validated = $request->validated();
        abort_if($course->students()->whereKey($validated['user_id'])->exists(), 409, 'Mahasiswa sudah terdaftar di mata kuliah ini.');
        $course->students()->attach($validated['user_id'], ['enrolled_at' => now()]);

        return response()->json(['data' => ['message' => 'Mahasiswa berhasil didaftarkan.']], 201);
    }

    public function destroy(Request $request, Course $course, User $user): JsonResponse
    {
        Gate::authorize('manageEnrollment', $course);
        abort_unless($user->role === 'mahasiswa', 404);
        $course->students()->detach($user->id);

        return response()->json(['data' => ['message' => 'Mahasiswa dikeluarkan dari mata kuliah.']]);
    }
}
