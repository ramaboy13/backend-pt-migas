<?php
// app/Http/Requests/VerifyEmailRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    public function rules(): array
    {
        if ($this->isMethod('GET')) {
            return [
                'token' => ['required', 'string']
            ];
        }

        return [
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6']
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Token verifikasi diperlukan',
            'email.required' => 'Email diperlukan',
            'email.email' => 'Format email tidak valid',
            'code.required' => 'Kode verifikasi diperlukan',
            'code.size' => 'Kode verifikasi harus 6 digit'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'data' => null,
                'errors' => $validator->errors()
            ], 422)
        );
    }
}