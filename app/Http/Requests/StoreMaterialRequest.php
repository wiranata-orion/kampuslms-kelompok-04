<?php

namespace App\Http\Requests;

use App\Models\Course;
use App\Models\Material;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course instanceof Course
            && Gate::allows('create', [Material::class, $course]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:file,link'],
            'file' => ['required_if:type,file', 'nullable', 'file', 'max:10240'],
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
            'file.required_if' => 'File materi wajib diunggah.',
            'file.file' => 'Materi harus berupa file yang valid.',
            'file.max' => 'Ukuran file materi maksimal 10 MB.',
            'external_url.required_if' => 'URL materi wajib diisi.',
            'external_url.url' => 'Format URL materi tidak valid.',
        ];
    }
}
