<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'user_id' => ['required', 'exists:users,id'],
		];
	}

	public function messages(): array
	{
		return [
			'user_id.required' => 'Mahasiswa wajib dipilih.',
			'user_id.exists' => 'Mahasiswa yang dipilih tidak ditemukan.',
		];
	}
}
