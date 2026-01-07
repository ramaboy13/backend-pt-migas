<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TabungRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('POST');

        return [
            'nama' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:100'],
            'berat' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'min:0', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama tabung wajib diisi',
            'nama.string' => 'Nama tabung harus berupa teks',
            'nama.max' => 'Nama tabung tidak boleh lebih dari 100 karakter',
            'berat.required' => 'Berat tabung wajib diisi',
            'berat.numeric' => 'Berat tabung harus berupa angka',
            'berat.min' => 'Berat tabung tidak boleh bernilai negatif',
            'berat.max' => 'Berat tabung tidak boleh melebihi 1000 kg',
        ];

    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'data' => null,
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
