<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    // TODO: ganti dengan pengecekan hak akses sungguhan (Policy) di minggu 7
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('courses', 'code')->ignore($this->course),
            ],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'sks' => ['required', 'integer', 'between:1,6'],
            'lecturer_id' => ['required', 'exists:users,id'],
            'status' => ['required', 'in:draft,active,archived'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode mata kuliah wajib diisi.',
            'code.unique' => 'Kode mata kuliah ini sudah dipakai.',
            'code.max' => 'Kode mata kuliah maksimal 20 karakter.',
            'name.required' => 'Nama mata kuliah wajib diisi.',
            'name.max' => 'Nama mata kuliah maksimal 150 karakter.',
            'sks.required' => 'SKS wajib diisi.',
            'sks.integer' => 'SKS harus berupa angka.',
            'sks.between' => 'SKS harus antara 1 sampai 6.',
            'lecturer_id.required' => 'Dosen pengampu wajib dipilih.',
            'lecturer_id.exists' => 'Dosen yang dipilih tidak ditemukan.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status harus salah satu dari: draft, active, atau archived.',
        ];
    }
}