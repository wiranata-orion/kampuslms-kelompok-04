<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'created_by' => $this->created_by,
            'title' => $this->title,
            'instructions' => $this->instructions,
            'due_at' => $this->due_at?->toIso8601String(),
            'max_score' => $this->max_score,
            'allow_late' => $this->allow_late,
            'status' => $this->status,
            'course' => $this->whenLoaded('course', fn () => [
                'id' => $this->course->id,
                'code' => $this->course->code,
                'name' => $this->course->name,
            ]),
            'permissions' => $this->when($request->routeIs('api.v1.assignments.show'), fn () => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
