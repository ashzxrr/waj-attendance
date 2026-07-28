<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pin' => ['required', 'string'],
            'pin_absensi' => ['required', 'string', 'max:6'],
            'device_id' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'pin.required' => 'PIN karyawan wajib diisi.',
            'pin_absensi.required' => 'Kode absensi wajib diisi.',
            'pin_absensi.max' => 'Kode absensi maksimal 6 digit.',
        ];
    }
}