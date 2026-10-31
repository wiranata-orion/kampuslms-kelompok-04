<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = match ($user->role) {
            'admin' => [
                'total_users' => User::count(),
                'total_courses' => Course::count(),
            ],
            'dosen' => [
                'total_courses' => $user->taughtCourses()->count(),
                'total_assignments' => $user->taughtCourses()
                    ->withCount('assignments')
                    ->get()
                    ->sum('assignments_count'),
            ],
            'mahasiswa' => [
                'total_courses' => $user->courses()->count(),
                'total_submissions' => $user->submissions()->count(),
            ],
            default => [],
        };

        return response()->json([
            'data' => [
                'user' => (new UserResource($user))->resolve($request),
                'stats' => $stats,
            ],
        ]);
    }
}
