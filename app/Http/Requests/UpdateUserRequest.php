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
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'role' => ['required', 'in:admin,dosen,mahasiswa'],
            'nim_nip' => ['nullable', 'string', Rule::unique('users', 'nim_nip')->ignore($this->route('user'))],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 150 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
            'nim_nip.string' => 'NIM/NIP harus berupa teks.',
            'nim_nip.unique' => 'NIM/NIP ini sudah terdaftar.',
        ];
    }
}