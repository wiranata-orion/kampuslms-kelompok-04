<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseScheduleRequest;
use App\Http\Requests\UpdateCourseScheduleRequest;
use App\Http\Resources\CourseScheduleResource;
use App\Models\Course;
use App\Models\CourseSchedule;
use App\Services\CourseScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CourseScheduleController extends Controller
{
    public function index(Request $request, Course $course): JsonResponse
    {
        Gate::authorize('view', $course);

        return response()->json([
            'data' => CourseScheduleResource::collection(
                $course->schedules()->where('is_active', true)->orderBy('weekday')->orderBy('starts_at')->get()
            )->resolve($request),
        ]);
    }

    public function store(
        StoreCourseScheduleRequest $request,
        Course $course,
        CourseScheduleService $schedules,
    ): JsonResponse {
        Gate::authorize('manageEnrollment', $course);
        $attributes = $this->normalized($request->validated());
        $conflicts = collect();
        $schedule = DB::transaction(function () use ($course, $attributes, $schedules, &$conflicts) {
            $conflicts = $schedules->findConflicts($course, $attributes);
            if ($conflicts->isNotEmpty()) {
                return null;
            }

            $schedule = $course->schedules()->create($attributes);
            $schedules->syncSessions($schedule);

            return $schedule;
        });

        if ($conflicts->isNotEmpty()) {
            return $this->conflictResponse($conflicts, $course, $attributes, $schedules);
        }

        return response()->json([
            'data' => (new CourseScheduleResource($schedule))->resolve($request),
        ], 201);
    }

    public function update(
        UpdateCourseScheduleRequest $request,
        CourseSchedule $courseSchedule,
        CourseScheduleService $schedules,
    ): JsonResponse {
        $course = $courseSchedule->course;
        Gate::authorize('manageEnrollment', $course);

        abort_unless($courseSchedule->is_active, 404);
        $attributes = $this->normalized($request->validated());
        $conflicts = collect();
        DB::transaction(function () use ($course, $courseSchedule, $attributes, $schedules, &$conflicts) {
            $conflicts = $schedules->findConflicts($course, $attributes, $courseSchedule);
            if ($conflicts->isNotEmpty()) {
                return;
            }

            $courseSchedule->update($attributes);
            $schedules->syncSessions($courseSchedule);
        });

        if ($conflicts->isNotEmpty()) {
            return $this->conflictResponse($conflicts, $course, $attributes, $schedules, $courseSchedule);
        }

        return response()->json([
            'data' => (new CourseScheduleResource($courseSchedule->fresh()))->resolve($request),
        ]);
    }

    public function destroy(
        Request $request,
        CourseSchedule $courseSchedule,
        CourseScheduleService $schedules,
    ): JsonResponse {
        Gate::authorize('manageEnrollment', $courseSchedule->course);

        DB::transaction(function () use ($courseSchedule, $schedules) {
            $courseSchedule->update(['is_active' => false]);
            $schedules->syncSessions($courseSchedule);
        });

        return response()->json(['data' => ['message' => 'Jadwal dinonaktifkan.']]);
    }

    private function normalized(array $attributes): array
    {
        $attributes['room'] = mb_strtoupper(trim($attributes['room']));

        return $attributes;
    }

    private function conflictResponse(
        $conflicts,
        Course $course,
        array $attributes,
        CourseScheduleService $schedules,
        ?CourseSchedule $except = null,
    ): JsonResponse {
        $weekdayNames = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        $details = $conflicts->map(function (CourseSchedule $conflict) use ($course, $attributes, $weekdayNames) {
            $reasons = [];
            if ($conflict->course->lecturer_id === $course->lecturer_id) {
                $reasons[] = 'dosen pengampu yang sama';
            }
            if (mb_strtolower($conflict->room) === mb_strtolower($attributes['room'])) {
                $reasons[] = 'ruang yang sama';
            }

            return [
                'course_id' => $conflict->course_id,
                'course_code' => $conflict->course->code,
                'course_name' => $conflict->course->name,
                'weekday' => $conflict->weekday,
                'weekday_name' => $weekdayNames[$conflict->weekday],
                'starts_at' => substr((string) $conflict->starts_at, 0, 5),
                'ends_at' => substr((string) $conflict->ends_at, 0, 5),
                'room' => $conflict->room,
                'reasons' => $reasons,
            ];
        });

        $reasons = $details->flatMap(fn (array $detail) => $detail['reasons'])->unique()->values();
        $suggestions = $schedules->suggestAlternatives($course, $attributes, $except);
        foreach ($suggestions as &$suggestion) {
            $suggestion['weekday_name'] = $weekdayNames[$suggestion['weekday']];
        }

        return response()->json([
            'message' => 'Jadwal ditolak karena bertabrakan dengan jadwal lain: '.$reasons->join(' dan ').'.',
            'conflicts' => $details->values(),
            'suggestions' => $suggestions,
        ], 409);
    }
}
