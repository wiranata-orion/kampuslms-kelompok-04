<?php

namespace App\Http\Requests;

use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $assignment = $this->route('assignment');

        return $assignment instanceof Assignment
            && Gate::allows('create', [Submission::class, $assignment]);
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $assignment = $this->route('assignment');

            if ($assignment instanceof Assignment
                && ! $assignment->allow_late
                && now()->greaterThan($assignment->due_at)) {
                $validator->errors()->add('file', 'Batas waktu pengumpulan sudah lewat.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File tugas wajib diunggah.',
            'file.file' => 'Pengumpulan harus berupa file yang valid.',
            'file.mimes' => 'Format file tugas harus berupa PDF, DOC, atau DOCX.',
            'file.max' => 'Ukuran file tugas maksimal 5 MB.',
            'note.string' => 'Catatan harus berupa teks.',
        ];
    }
}
