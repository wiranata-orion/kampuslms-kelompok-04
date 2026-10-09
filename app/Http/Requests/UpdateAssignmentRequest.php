<?php

namespace App\Http\Requests;

use App\Models\Assignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateAssignmentRequest extends FormRequest
{
	public function authorize(): bool
	{
		$assignment = $this->route('assignment');

		return $assignment instanceof Assignment
			&& Gate::allows('update', $assignment);
	}

	public function rules(): array
	{
		$required = $this->isMethod('put') ? 'required' : 'sometimes';

		return [
			'course_id' => ['prohibited'],
			'title' => [$required, 'string', 'max:150'],
			'instructions' => [$required, 'string'],
			'due_at' => [$required, 'date'],
			'max_score' => ['nullable', 'integer', 'between:1,100'],
			'allow_late' => ['nullable', 'boolean'],
			'status' => [$required, 'in:draft,published'],
		];
	}

	public function messages(): array
	{
		return [
			'title.required' => 'Judul tugas wajib diisi.',
			'title.string' => 'Judul tugas harus berupa teks.',
			'title.max' => 'Judul tugas maksimal 150 karakter.',
			'instructions.required' => 'Instruksi tugas wajib diisi.',
			'instructions.string' => 'Instruksi tugas harus berupa teks.',
			'due_at.required' => 'Batas waktu pengumpulan wajib diisi.',
			'due_at.date' => 'Format batas waktu pengumpulan tidak valid.',
			'max_score.integer' => 'Nilai maksimum harus berupa bilangan bulat.',
			'max_score.between' => 'Nilai maksimum harus antara 1 sampai 100.',
			'allow_late.boolean' => 'Pengaturan keterlambatan tidak valid.',
			'status.required' => 'Status tugas wajib dipilih.',
			'status.in' => 'Status tugas tidak valid.',
		];
	}
}
