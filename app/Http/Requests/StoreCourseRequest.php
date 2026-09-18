<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    // TODO: ganti dengan pengecekan hak akses sungguhan (Policy) di minggu 7
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'unique:courses,code'],
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
            'name.required' => 'Nama mata kuliah wajib diisi.',
            'sks.between' => 'SKS harus antara 1 sampai 6.',
            'lecturer_id.required' => 'Dosen pengampu wajib dipilih.',
            'lecturer_id.exists' => 'Dosen yang dipilih tidak ditemukan.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
        ];
    }
}