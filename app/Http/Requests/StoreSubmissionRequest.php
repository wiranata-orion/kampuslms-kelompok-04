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
            'file' => ['required', 'file', 'max:10240'],
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
            'file.max' => 'Ukuran file tugas maksimal 10 MB.',
            'note.string' => 'Catatan harus berupa teks.',
        ];
    }
}
