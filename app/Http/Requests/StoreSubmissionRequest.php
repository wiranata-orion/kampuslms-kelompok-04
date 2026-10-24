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

        if (! $assignment instanceof Assignment
            || ! Gate::allows('create', [Submission::class, $assignment])) {
            return false;
        }

        $existing = Submission::where('assignment_id', $assignment->id)
            ->where('user_id', $this->user()?->id)
            ->first();

        if ($existing && $existing->grade()->exists()) {
            return false;
        }

        return true;
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
            // Deteksi jika server memotong payload karena melebihi post_max_size
            if (empty($_FILES) && empty($_POST) && (int) request()->server('CONTENT_LENGTH') > 0) {
                $validator->errors()->add('file', 'Ukuran file tugas melebihi batas upload server (maksimal 5 MB).');
            }

            // Deteksi jika berkas terkena batasan ukuran upload PHP
            if (request()->hasFile('file')) {
                $file = request()->file('file');
                if (! $file->isValid()) {
                    if (in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                        $validator->errors()->add('file', 'Ukuran file tugas melebihi batas maksimal 5 MB.');
                    } else {
                        $validator->errors()->add('file', 'Gagal mengunggah berkas tugas. Pastikan ukuran file tidak melebihi 5 MB.');
                    }
                }
            }

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
            'file.uploaded' => 'Ukuran file tugas melebihi batas maksimal 5 MB.',
            'file.mimes' => 'Format file tugas harus berupa PDF, DOC, atau DOCX.',
            'file.max' => 'Ukuran file tugas maksimal 5 MB.',
            'note.string' => 'Catatan harus berupa teks.',
        ];
    }
}
