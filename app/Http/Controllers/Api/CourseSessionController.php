<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseSessionResource;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\User;
use App\Services\AttendanceCodeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CourseSessionController extends Controller
{
    public function index(Request $request, Course $course)
    {
        Gate::authorize('view', $course);
        $validated = $request->validate([
            'from' => ['sometimes', 'date_format:Y-m-d'],
            'until' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $timezone = config('lms.timezone');
        $from = $validated['from'] ?? now($timezone)->toDateString();
        $until = $validated['until'] ?? now($timezone)->addDays(30)->toDateString();

        $sessions = $course->sessions()
            ->where('status', 'scheduled')
            ->whereBetween('session_date', [$from, $until])
            ->with('course:id,code,name')
            ->when(
                $request->user()->role === 'mahasiswa',
                fn ($query) => $query->with([
                    'attendanceRecords' => fn ($attendanceQuery) => $attendanceQuery->where('user_id', $request->user()->id),
                ]),
            )
            ->orderBy('starts_at')
            ->paginate(20);

        return ApiResponse::collection($sessions, CourseSessionResource::class, $request);
    }

    public function attendanceCode(
        Request $request,
        CourseSession $courseSession,
        AttendanceCodeService $codes,
    ): JsonResponse {
        Gate::authorize('manageEnrollment', $courseSession->course);
        abort_if($courseSession->status !== 'scheduled', 409, 'Sesi ini tidak menerima presensi.');
        abort_if(now('UTC')->lt($courseSession->starts_at) || now('UTC')->gt($courseSession->ends_at), 409, 'Kode presensi hanya tersedia selama sesi berlangsung.');

        return response()->json([
            'data' => [
                'session_id' => $courseSession->id,
                'code' => $codes->forSession($courseSession),
                'valid_from' => $courseSession->starts_at->toIso8601String(),
                'valid_until' => $courseSession->ends_at->toIso8601String(),
            ],
        ]);
    }

    public function attend(
        Request $request,
        CourseSession $courseSession,
        AttendanceCodeService $codes,
    ): JsonResponse {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64'],
            'source' => ['sometimes', Rule::in(['code', 'qr'])],
        ]);
        $course = $courseSession->course;

        Gate::authorize('view', $course);
        abort_unless($request->user()->role === 'mahasiswa', 403, 'Hanya mahasiswa yang dapat mengisi presensi.');
        abort_unless($course->students()->whereKey($request->user()->id)->exists(), 403);
        abort_if($courseSession->status !== 'scheduled', 409, 'Sesi ini tidak menerima presensi.');
        abort_if(now('UTC')->lt($courseSession->starts_at) || now('UTC')->gt($courseSession->ends_at), 409, 'Masa presensi sesi ini belum dimulai atau telah berakhir.');
        abort_unless($codes->matches($courseSession, $validated['code']), 422, 'Kode presensi tidak valid.');

        $alreadyRecorded = DB::transaction(function () use ($request, $courseSession, $validated) {
            User::query()->whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            if ($courseSession->attendanceRecords()->where('user_id', $request->user()->id)->exists()) {
                return true;
            }

            $courseSession->attendanceRecords()->create([
                'user_id' => $request->user()->id,
                'attended_at' => now('UTC'),
                'source' => $validated['source'] ?? 'code',
            ]);

            return false;
        });

        if ($alreadyRecorded) {
            return response()->json(['message' => 'Presensi untuk sesi ini sudah tercatat.'], 409);
        }

        return response()->json([
            'data' => ['message' => 'Presensi berhasil dicatat.'],
        ], 201);
    }
}
