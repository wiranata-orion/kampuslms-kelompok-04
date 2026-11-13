<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'weekday' => ['required', 'integer', 'between:1,7'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'room' => ['required', 'string', 'max:100'],
            'effective_from' => ['required', 'date_format:Y-m-d'],
            'effective_until' => ['required', 'date_format:Y-m-d', 'after_or_equal:effective_from'],
        ];
    }
}
