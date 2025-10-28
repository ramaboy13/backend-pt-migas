<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TabungRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Semua user yang sudah login bisa akses
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('POST');

        return [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:100'],
            'berat' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'min:0', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Cylinder name is required',
            'name.string' => 'Cylinder name must be a text',
            'name.max' => 'Cylinder name may not exceed 100 characters',
            'berat.required' => 'Cylinder weight is required',
            'berat.numeric' => 'Cylinder weight must be a number',
            'berat.min' => 'Cylinder weight cannot be negative',
            'berat.max' => 'Cylinder weight may not exceed 1000 kg',
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
