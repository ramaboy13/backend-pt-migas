<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class CreateReferralCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
       return Auth::check() && Auth::user()->hasRole('super_admin');
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in(['admin'])],
            'max_uses' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'expires_at' => ['sometimes', 'date', 'after:today']
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Role wajib dipilih',
            'role.in' => 'Role tidak valid. Hanya "admin" yang diperbolehkan',
            'max_uses.integer' => 'Max uses harus berupa angka',
            'max_uses.min' => 'Max uses minimal 1',
            'max_uses.max' => 'Max uses maksimal 100',
            'expires_at.date' => 'Format tanggal tidak valid',
            'expires_at.after' => 'Tanggal harus setelah hari ini'
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

    protected function prepareForValidation()
    {
        if ($this->has('expires_at') && !empty($this->expires_at)) {
            $this->merge([
                'expires_at' => \Carbon\Carbon::parse($this->expires_at)->toDateTimeString()
            ]);
        }
    }
}