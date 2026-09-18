<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    // TODO: ganti dengan pengecekan hak akses sungguhan (Policy) di minggu 7
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user)],
            'role' => ['required', 'in:admin,dosen,mahasiswa'],
            'nim_nip' => ['nullable', 'string', Rule::unique('users', 'nim_nip')->ignore($this->user)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
            'nim_nip.unique' => 'NIM/NIP ini sudah terdaftar.',
        ];
    }
}