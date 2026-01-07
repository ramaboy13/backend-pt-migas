<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class PangkalanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('POST');
        $pangkalanId = $this->route('id');

        return [
            'regist_id' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'digits:10', Rule::unique('tb_pangkalan', 'regist_id')->ignore($pangkalanId)],
            'nama' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:255'],
            'no_ktp' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'digits:16', Rule::unique('tb_pangkalan', 'no_ktp')->ignore($pangkalanId)],
            'alamat' => ['nullable', 'string'],
            'harga_satuan' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'regist_id.required' => 'ID registrasi wajib diisi',
            'regist_id.unique' => 'ID registrasi sudah terdaftar',
            'regist_id.numeric' => 'ID registrasi harus berupa angka',
            'regist_id.digits' => 'ID registrasi harus terdiri dari 10 digit',
            'nama.required' => 'Nama wajib diisi',
            'nama.max' => 'Nama tidak boleh lebih dari 255 karakter',
            'no_ktp.required' => 'Nomor KTP wajib diisi',
            'no_ktp.unique' => 'Nomor KTP sudah terdaftar',
            'no_ktp.numeric' => 'Nomor KTP harus berupa angka',
            'no_ktp.digits' => 'Nomor KTP harus terdiri dari 16 digit',
            'harga_satuan.numeric' => 'Harga satuan harus berupa angka',
            'harga_satuan.min' => 'Harga satuan tidak boleh bernilai negatif',
        ];

    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
