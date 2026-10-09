<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
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
            'uploaded_by' => $this->uploaded_by,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'original_name' => $this->original_name,
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
            'external_url' => $this->external_url,
            'download_available' => filled($this->file_path),
            'uploader' => $this->whenLoaded('uploader', fn () => new UserResource($this->uploader)),
            'course' => $this->whenLoaded('course', fn () => [
                'id' => $this->course->id,
                'code' => $this->course->code,
                'name' => $this->course->name,
            ]),
            'permissions' => $this->when($request->routeIs('api.v1.materials.show', 'api.v1.courses.materials'), fn () => [
                'view' => true,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
                'can_update' => $request->user()?->can('update', $this->resource) ?? false,
                'can_delete' => $request->user()?->can('delete', $this->resource) ?? false,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
