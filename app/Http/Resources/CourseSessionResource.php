<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'course_schedule_id' => $this->course_schedule_id,
            'session_date' => $this->session_date?->toDateString(),
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'room' => $this->room,
            'status' => $this->status,
            'course' => $this->whenLoaded('course', fn () => [
                'id' => $this->course->id,
                'code' => $this->course->code,
                'name' => $this->course->name,
            ]),
            'attendance_recorded' => $this->whenLoaded(
                'attendanceRecords',
                fn () => $this->attendanceRecords->isNotEmpty(),
            ),
        ];
    }
}
