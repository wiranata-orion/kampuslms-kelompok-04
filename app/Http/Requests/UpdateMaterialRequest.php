<?php

namespace App\Http\Requests;

use App\Models\Material;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateMaterialRequest extends FormRequest
{
	public function authorize(): bool
	{
		$material = $this->route('material');

		return $material instanceof Material
			&& Gate::allows('update', $material);
	}

	public function rules(): array
	{
		return [
			'title' => ['required', 'string', 'max:150'],
			'description' => ['nullable', 'string'],
			'type' => ['required', 'in:file,link'],
			'file' => ['prohibited'],
			'original_name' => ['required_if:type,file', 'nullable', 'string', 'max:255'],
			'file_size' => ['required_if:type,file', 'nullable', 'integer', 'min:0'],
			'mime_type' => ['nullable', 'string', 'max:255'],
			'external_url' => ['required_if:type,link', 'nullable', 'url'],
		];
	}

	public function messages(): array
	{
		return [
			'title.required' => 'Judul materi wajib diisi.',
			'title.string' => 'Judul materi harus berupa teks.',
			'title.max' => 'Judul materi maksimal 150 karakter.',
			'description.string' => 'Deskripsi materi harus berupa teks.',
			'type.required' => 'Jenis materi wajib dipilih.',
			'type.in' => 'Jenis materi tidak valid.',
			'file.prohibited' => 'Unggah berkas materi belum tersedia; isi metadata berkas saja.',
			'original_name.required_if' => 'Nama berkas materi wajib diisi.',
			'file_size.required_if' => 'Ukuran berkas materi wajib diisi.',
			'file_size.integer' => 'Ukuran berkas harus berupa bilangan bulat.',
			'file_size.min' => 'Ukuran berkas tidak boleh negatif.',
			'external_url.required_if' => 'URL materi wajib diisi.',
			'external_url.url' => 'Format URL materi tidak valid.',
		];
	}
}
