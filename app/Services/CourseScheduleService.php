<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\CourseSession;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class CourseScheduleService
{
    public function findConflicts(Course $course, array $attributes, ?CourseSchedule $except = null): Collection
    {
        return CourseSchedule::query()
            ->with('course:id,code,name,lecturer_id')
            ->where('is_active', true)
            ->where('weekday', $attributes['weekday'])
            ->whereDate('effective_from', '<=', $attributes['effective_until'])
            ->whereDate('effective_until', '>=', $attributes['effective_from'])
            ->when($except, fn ($query) => $query->whereKeyNot($except->id))
            ->where(function ($query) use ($course, $attributes) {
                $query->whereRaw('LOWER(room) = ?', [mb_strtolower(trim($attributes['room']))])
                    ->orWhereHas('course', fn ($courseQuery) => $courseQuery->where('lecturer_id', $course->lecturer_id));
            })
            ->where('starts_at', '<', $attributes['ends_at'])
            ->where('ends_at', '>', $attributes['starts_at'])
            ->lockForUpdate()
            ->get();
    }

    public function suggestAlternatives(Course $course, array $attributes, ?CourseSchedule $except = null): array
    {
        $busySchedules = CourseSchedule::query()
            ->with('course:id,lecturer_id')
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $attributes['effective_until'])
            ->whereDate('effective_until', '>=', $attributes['effective_from'])
            ->when($except, fn ($query) => $query->whereKeyNot($except->id))
            ->where(function ($query) use ($course, $attributes) {
                $query->whereRaw('LOWER(room) = ?', [mb_strtolower(trim($attributes['room']))])
                    ->orWhereHas('course', fn ($courseQuery) => $courseQuery->where('lecturer_id', $course->lecturer_id));
            })
            ->get();

        $duration = $this->minutesBetween($attributes['starts_at'], $attributes['ends_at']);
        $suggestions = [];
        $days = range(1, 7);
        $days = array_merge(array_slice($days, $attributes['weekday'] - 1), array_slice($days, 0, $attributes['weekday'] - 1));

        foreach ($days as $weekday) {
            for ($startMinute = 7 * 60; $startMinute + $duration <= 21 * 60; $startMinute += 30) {
                $start = sprintf('%02d:%02d', intdiv($startMinute, 60), $startMinute % 60);
                $endMinute = $startMinute + $duration;
                $end = sprintf('%02d:%02d', intdiv($endMinute, 60), $endMinute % 60);

                if (
                    $weekday === (int) $attributes['weekday']
                    && $start === substr($attributes['starts_at'], 0, 5)
                ) {
                    continue;
                }

                $hasConflict = $busySchedules->contains(fn (CourseSchedule $schedule) =>
                    (int) $schedule->weekday === $weekday
                    && substr((string) $schedule->starts_at, 0, 5) < $end
                    && substr((string) $schedule->ends_at, 0, 5) > $start
                );

                if (! $hasConflict) {
                    $suggestions[] = [
                        'weekday' => $weekday,
                        'starts_at' => $start,
                        'ends_at' => $end,
                        'room' => trim($attributes['room']),
                    ];

                    if (count($suggestions) === 3) {
                        return $suggestions;
                    }
                }
            }
        }

        return $suggestions;
    }

    public function syncSessions(CourseSchedule $schedule): void
    {
        $desiredDates = [];
        $date = CarbonImmutable::parse($schedule->effective_from->toDateString());
        $lastDate = CarbonImmutable::parse($schedule->effective_until->toDateString());

        for (; $date->lessThanOrEqualTo($lastDate); $date = $date->addDay()) {
            if ($date->dayOfWeekIso === (int) $schedule->weekday) {
                $desiredDates[] = $date->toDateString();
            }
        }

        $existing = $schedule->sessions()->withCount('attendanceRecords')->get()->keyBy(
            fn (CourseSession $session) => $session->session_date->toDateString(),
        );
        $now = now('UTC');

        foreach ($existing as $sessionDate => $session) {
            if (in_array($sessionDate, $desiredDates, true)) {
                continue;
            }

            if ($session->attendance_records_count === 0 && $session->starts_at->greaterThan($now)) {
                $session->delete();
            } elseif ($session->starts_at->greaterThan($now)) {
                $session->update(['status' => 'cancelled']);
            }
        }

        if (! $schedule->is_active) {
            foreach ($existing as $session) {
                if ($session->attendance_records_count === 0 && $session->starts_at->greaterThan($now)) {
                    $session->delete();
                } elseif ($session->starts_at->greaterThan($now)) {
                    $session->update(['status' => 'cancelled']);
                }
            }

            return;
        }

        foreach ($desiredDates as $sessionDate) {
            [$startsAt, $endsAt] = $this->sessionTimes($schedule, $sessionDate);
            $session = $existing->get($sessionDate);

            if ($session) {
                if ($session->attendance_records_count === 0 && $session->starts_at->greaterThan($now)) {
                    $session->update([
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                        'room' => $schedule->room,
                    ]);
                }

                continue;
            }

            $schedule->sessions()->create([
                'course_id' => $schedule->course_id,
                'session_date' => $sessionDate,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'room' => $schedule->room,
                'status' => 'scheduled',
            ]);
        }
    }

    private function sessionTimes(CourseSchedule $schedule, string $date): array
    {
        $timezone = config('lms.timezone');
        $startsAt = CarbonImmutable::parse($date.' '.$schedule->starts_at, $timezone);
        $endsAt = CarbonImmutable::parse($date.' '.$schedule->ends_at, $timezone);

        return [$startsAt->utc(), $endsAt->utc()];
    }

    private function minutesBetween(string $start, string $end): int
    {
        [$startHour, $startMinute] = array_map('intval', explode(':', substr($start, 0, 5)));
        [$endHour, $endMinute] = array_map('intval', explode(':', substr($end, 0, 5)));

        return ($endHour * 60 + $endMinute) - ($startHour * 60 + $startMinute);
    }
}
