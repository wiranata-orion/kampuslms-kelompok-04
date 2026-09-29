<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		$grade = $this->route('grade');
		$maxScore = $grade->submission->assignment->max_score;

		return [
			'score' => ['required', 'numeric', 'min:0', "max:{$maxScore}"],
			'feedback' => ['nullable', 'string'],
		];
	}

	public function messages(): array
	{
		return [
			'score.required' => 'Nilai wajib diisi.',
			'score.numeric' => 'Nilai harus berupa angka.',
			'score.min' => 'Nilai tidak boleh kurang dari 0.',
			'score.max' => 'Nilai tidak boleh melebihi nilai maksimum tugas.',
			'feedback.string' => 'Feedback harus berupa teks.',
		];
	}
}
