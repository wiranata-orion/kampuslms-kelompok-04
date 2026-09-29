<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubmissionRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'file' => ['nullable', 'file', 'max:10240'],
			'note' => ['nullable', 'string'],
		];
	}

	public function messages(): array
	{
		return [
			'file.file' => 'Pengumpulan harus berupa file yang valid.',
			'file.max' => 'Ukuran file tugas maksimal 10 MB.',
			'note.string' => 'Catatan harus berupa teks.',
		];
	}
}
