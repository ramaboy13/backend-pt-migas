<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('POST');

        $rules = [
            'nama' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:255'],
            'identitas' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:100'],
            'jumlah' => [$isCreate ? 'required' : 'sometimes', 'integer', 'min:0'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'status' => [$isCreate ? 'required' : 'sometimes', 'boolean'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $assetId = $this->route('id');
            $rules['identitas'] = ['sometimes', 'string', 'max:100', 'unique:tb_asset,identitas,'.$assetId];
        } else {
            $rules['identitas'] = ['required', 'string', 'max:100', 'unique:tb_asset,identitas'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama aset wajib diisi',
            'nama.max' => 'Nama aset maksimal 255 karakter',
            'identitas.required' => 'Identitas aset wajib diisi',
            'identitas.unique' => 'Identitas aset sudah terdaftar',
            'identitas.max' => 'Identitas aset maksimal 100 karakter',
            'jumlah.required' => 'Jumlah aset wajib diisi',
            'jumlah.integer' => 'Jumlah aset harus berupa angka',
            'jumlah.min' => 'Jumlah aset tidak boleh kurang dari 0',
            'catatan.max' => 'Catatan maksimal 500 karakter',
            'status.required' => 'Status RFU wajib diisi',
            'status.boolean' => 'Status RFU harus bernilai true atau false',
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
