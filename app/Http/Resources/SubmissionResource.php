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
            'note' => $this->note,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'is_late' => $this->is_late,
            'student' => $this->whenLoaded('student', fn () => new PublicUserResource($this->student)),
            'assignment' => $this->whenLoaded('assignment', fn () => new AssignmentResource($this->assignment)),
            'grade' => $this->whenLoaded('grade', fn () => new GradeResource($this->grade)),
            'permissions' => $this->when($request->routeIs('api.v1.submissions.show'), fn () => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
            ]),
        ];
    }
}
