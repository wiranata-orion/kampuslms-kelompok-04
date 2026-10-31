<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GradeSummaryResource;
use App\Models\Course;
use App\Models\Grade;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GradeSummaryController extends Controller
{
    public function course(Request $request, Course $course)
    {
        Gate::authorize('viewCourseSummary', [Grade::class, $course]);

        $grades = Grade::query()
            ->whereHas('submission.assignment', fn ($query) => $query->where('course_id', $course->id))
            ->with(['submission.student', 'submission.assignment.course'])
            ->latest('graded_at')
            ->orderByDesc('id')
            ->paginate(15);

        return ApiResponse::collection($grades, GradeSummaryResource::class, $request);
    }

    public function mine(Request $request)
    {
        Gate::authorize('viewOwnSummary', Grade::class);

        $student = $request->user();
        $grades = Grade::query()
            ->whereNotNull('published_at')
            ->whereHas('submission', function ($query) use ($student) {
                $query->where('user_id', $student->id)
                    ->whereHas('assignment.course.students', fn ($students) => $students->whereKey($student->id));
            })
            ->with(['submission.student', 'submission.assignment.course'])
            ->latest('graded_at')
            ->orderByDesc('id')
            ->paginate(15);

        return ApiResponse::collection($grades, GradeSummaryResource::class, $request);
    }
}
