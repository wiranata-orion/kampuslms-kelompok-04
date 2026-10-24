<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assignment_id' => $this->assignment_id,
            'user_id' => $this->user_id,
            'original_name' => $this->original_name,
            'file_size' => $this->file_size,
            'file_size_formatted' => $this->formatted_file_size,
            'note' => $this->note,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'is_late' => (bool) $this->is_late,
            'is_graded' => $this->relationLoaded('grade') ? $this->grade !== null : $this->grade()->exists(),
            'student' => $this->whenLoaded('student', fn () => new UserResource($this->student)),
            'assignment' => $this->whenLoaded('assignment', fn () => new AssignmentResource($this->assignment)),
            'grade' => $this->whenLoaded('grade', fn () => new GradeResource($this->grade)),
            'permissions' => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
            ],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
