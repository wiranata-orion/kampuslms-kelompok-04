<?php

namespace App\Http\Requests;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course instanceof Course
            && Gate::allows('manageEnrollment', $course);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mahasiswa')],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Mahasiswa wajib dipilih.',
            'user_id.integer' => 'ID mahasiswa harus berupa bilangan bulat.',
            'user_id.exists' => 'Mahasiswa yang dipilih tidak valid atau bukan mahasiswa.',
        ];
    }
}
