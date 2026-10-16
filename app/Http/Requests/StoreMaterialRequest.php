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

    protected function prepareForValidation(): void
    {
        if ($this->hasFile('file') && $this->input('type') === 'file') {
            $file = $this->file('file');
            $this->merge([
                'original_name' => $this->input('original_name') ?: $file->getClientOriginalName(),
                'file_size' => $this->input('file_size') ?: $file->getSize(),
                'mime_type' => $this->input('mime_type') ?: $file->getMimeType(),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:file,link'],
            'file' => ['nullable', 'file', 'mimes:pdf,pptx,docx,doc,ppt', 'max:20480'],
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
            'file.file' => 'Berkas materi harus berupa file yang valid.',
            'file.mimes' => 'Format file materi harus berupa PDF, PPTX, PPT, DOC, atau DOCX.',
            'file.max' => 'Ukuran berkas materi maksimal 20 MB.',
            'original_name.required_if' => 'Nama berkas materi wajib diisi.',
            'file_size.required_if' => 'Ukuran berkas materi wajib diisi.',
            'file_size.integer' => 'Ukuran berkas harus berupa bilangan bulat.',
            'file_size.min' => 'Ukuran berkas tidak boleh negatif.',
            'external_url.required_if' => 'URL materi wajib diisi.',
            'external_url.url' => 'Format URL materi tidak valid.',
        ];
    }
}
