<?php

namespace App\Http\Requests;

use App\Models\Material;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class UpdateMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        $material = $this->route('material');

        return $material instanceof Material
            && Gate::allows('update', $material);
    }

    protected function prepareForValidation(): void
    {
        $material = $this->route('material');
        if ($this->hasFile('file') && $this->input('type') === 'file') {
            $file = $this->file('file');
            if ($file->isValid()) {
                $this->merge([
                    'original_name' => $this->input('original_name') ?: $file->getClientOriginalName(),
                    'file_size' => $this->input('file_size') ?: $file->getSize(),
                    'mime_type' => $this->input('mime_type') ?: $file->getMimeType(),
                ]);
            }
        } elseif ($material instanceof Material && $this->input('type') === 'file') {
            $this->merge([
                'original_name' => $this->input('original_name') ?: $material->original_name,
                'file_size' => $this->input('file_size') ?: $material->file_size,
                'mime_type' => $this->input('mime_type') ?: $material->mime_type,
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('type') === 'file' && empty($_FILES) && empty($_POST) && (int) request()->server('CONTENT_LENGTH') > 0) {
                $validator->errors()->add('file', 'Ukuran berkas materi melebihi batas upload server (maksimal 20 MB).');
            }

            if ($this->hasFile('file')) {
                $file = $this->file('file');
                if (! $file->isValid()) {
                    if (in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                        $validator->errors()->add('file', 'Ukuran berkas materi melebihi batas maksimal 20 MB.');
                    } else {
                        $validator->errors()->add('file', 'Gagal mengunggah berkas materi. Pastikan ukuran file tidak melebihi 20 MB.');
                    }
                }
            }
        });
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
            'file.uploaded' => 'Ukuran berkas materi melebihi batas maksimal 20 MB.',
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
