<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lecturer_id' => $this->lecturer_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'sks' => $this->sks,
            'status' => $this->status,
            'lecturer' => $this->whenLoaded('lecturer', fn () => new UserResource($this->lecturer)),
            'counts' => [
                'materials' => $this->whenCounted('materials'),
                'assignments' => $this->whenCounted('assignments'),
            ],
            'permissions' => $this->when($request->routeIs('api.v1.courses.show'), fn () => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
                'manage_enrollment' => $request->user()?->can('manageEnrollment', $this->resource) ?? false,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
