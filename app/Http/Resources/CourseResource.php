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
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'sks' => $this->sks,
            'status' => $this->status,

            'lecturer' => $this->whenLoaded('lecturer', function () {
                return [
                    'id' => $this->lecturer->id,
                    'name' => $this->lecturer->name,
                    'email' => $this->lecturer->email,
                ];
            }),
        ];
    }
}