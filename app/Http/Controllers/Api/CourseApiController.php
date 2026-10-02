<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use Illuminate\Http\Request;

class CourseApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $courses = match ($user->role) {
            'dosen' => $user->taughtCourses()
                ->with('lecturer')
                ->orderBy('name')
                ->paginate(15),

            'mahasiswa' => $user->courses()
                ->with('lecturer')
                ->orderBy('name')
                ->paginate(15),

            default => abort(403),
        };

        return CourseResource::collection($courses);
    }
}