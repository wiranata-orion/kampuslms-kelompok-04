<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'weekday' => $this->weekday,
            'starts_at' => substr((string) $this->starts_at, 0, 5),
            'ends_at' => substr((string) $this->ends_at, 0, 5),
            'room' => $this->room,
            'effective_from' => $this->effective_from?->toDateString(),
            'effective_until' => $this->effective_until?->toDateString(),
            'is_active' => $this->is_active,
        ];
    }
}
