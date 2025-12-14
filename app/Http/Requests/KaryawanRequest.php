<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class KaryawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('POST');

        $rules = [
            'nik' => [$isCreate ? 'required' : 'sometimes', 'regex:/^[0-9]{16}$/', 'digits:16'],
            'nama' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:255'],
            'jabatan' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:100'],
            'gaji_pokok' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'bpjs_kesehatan' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'min:0', 'max:100'],
            'bpjs_tenagakerja' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'min:0', 'max:100'],
            'tgl_masuk' => [$isCreate ? 'required' : 'sometimes', 'date'],
            'aktif' => ['sometimes', 'boolean'],
            'alamat' => ['nullable', 'string']
        ];

        // Untuk update, tambahkan unique rule
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $karyawanId = $this->route('id');

            $rules['nik'] = [
                'sometimes',
                'regex:/^[0-9]{16}$/',
                'digits:16',
                'unique:tb_karyawan,nik,' . $karyawanId
            ];
        }
        // Untuk create, tambahkan unique rule 
        else {
            $rules['nik'] = [
                'required',
                'regex:/^[0-9]{16}$/',
                'digits:16',
                'unique:tb_karyawan,nik'
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nik.required' => 'nik is required',
            'nik.unique' => 'nik has already been registered',
            'nik.regex' => 'nik must be a number',
            'nik.digits' => 'nik must be 16 digits',
            'nama.required' => 'Name is required',
            'nama.max' => 'Name may not exceed 255 characters',
            'jabatan.required' => 'Position is required',
            'jabatan.max' => 'Position may not exceed 100 characters',
            'gaji_pokok.required' => 'Basic salary is required',
            'gaji_pokok.numeric' => 'Basic salary must be a number',
            'gaji_pokok.min' => 'Basic salary cannot be negative',
            'bpjs_kesehatan.max' => 'BPJS Health cannot exceed 100%',
            'bpjs_tenagakerja.max' => 'BPJS Employment cannot exceed 100%',
            'tgl_masuk.required' => 'Join date is required',
            'tgl_masuk.date' => 'Join date must be a valid date format',
            'aktif.boolean' => 'Active status must be true or false'
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
