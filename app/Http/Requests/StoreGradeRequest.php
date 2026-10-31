<?php

namespace App\Http\Requests;

use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreGradeRequest extends FormRequest
{
	public function authorize(): bool
	{
		$submission = $this->route('submission');

		if (! $submission instanceof Submission) {
			return false;
		}

		$submission->loadMissing(['assignment.course', 'grade']);
		$grade = $submission->grade;

		return Gate::allows($grade ? 'update' : 'create', $grade ?? [Grade::class, $submission]);
	}

	public function rules(): array
	{
		$submission = $this->route('submission');
		$maxScore = $submission->assignment->max_score;

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
