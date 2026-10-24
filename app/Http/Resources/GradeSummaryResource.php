<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $submission = $this->submission;

        return [
            'id' => $this->id,
            'student' => new PublicUserResource($submission->student),
            'course' => [
                'id' => $submission->assignment->course->id,
                'code' => $submission->assignment->course->code,
                'name' => $submission->assignment->course->name,
            ],
            'assignment' => [
                'id' => $submission->assignment->id,
                'title' => $submission->assignment->title,
                'max_score' => $submission->assignment->max_score,
            ],
            'score' => $this->score,
            'feedback' => $this->feedback,
            'graded_at' => $this->graded_at?->toIso8601String(),
            'published_at' => $this->published_at?->toIso8601String(),
            'is_published' => $this->published_at !== null,
        ];
    }
}
