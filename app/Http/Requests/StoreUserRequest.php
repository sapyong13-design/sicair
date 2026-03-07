<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'nip'            => 'required|string|max:30|unique:users,nip',
            'email'          => 'nullable|email|unique:users,email|max:255',
            'password'       => 'required|string|min:8',
            'role'           => 'required|string|in:admin,ketua,wakil_ketua,atasan,hakim,hakim_adhoc,panitera,sekretaris,kepegawaian,pegawai',
            'jabatan'        => 'nullable|string|max:100',
            'golongan_ruang' => 'nullable|string|max:10',
            'unit_kerja'     => 'nullable|string|max:100',
            'atasan_id'      => 'nullable|exists:users,id',
            'leave_balance'  => 'nullable|integer|min:0|max:365',
        ];
    }

    public function messages(): array
    {
        return [
            'nip.unique'      => 'NIP sudah terdaftar.',
            'email.unique'    => 'Email sudah terdaftar.',
            'password.min'    => 'Password minimal 8 karakter.',
            'role.in'         => 'Role tidak valid.',
        ];
    }
}
