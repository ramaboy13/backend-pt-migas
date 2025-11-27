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
            'NIK' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'digits:16'],
            'nama' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:255'],
            'jabatan' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:100'],
            'gapok' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'bpjs_kesehatan' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'min:0', 'max:100'],
            'bpjs_tenagakerja' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'min:0', 'max:100'],
            'tgl_masuk' => [$isCreate ? 'required' : 'sometimes', 'date'],
            'is_active' => ['sometimes', 'boolean'],
            'alamat' => ['nullable', 'string']
        ];

        // Untuk update, tambahkan unique rule dengan ignore
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $karyawanId = $this->route('id');
            $rules['NIK'] = ['sometimes', 'numeric', 'digits:16', 'unique:tb_karyawan,NIK,' . $karyawanId];
        } else {
            // Untuk create, tambahkan unique rule
            $rules['NIK'] = ['required', 'numeric', 'digits:16', 'unique:tb_karyawan,NIK'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'NIK.required' => 'NIK is required',
            'NIK.unique' => 'NIK has already been registered',
            'NIK.numeric' => 'NIK must be a number',
            'NIK.digits' => 'NIK must be 16 digits',
            'nama.required' => 'Name is required',
            'nama.max' => 'Name may not exceed 255 characters',
            'jabatan.required' => 'Position is required',
            'jabatan.max' => 'Position may not exceed 100 characters',
            'gapok.required' => 'Basic salary is required',
            'gapok.numeric' => 'Basic salary must be a number',
            'gapok.min' => 'Basic salary cannot be negative',
            'bpjs_kesehatan.required' => 'BPJS Health is required',
            'bpjs_kesehatan.numeric' => 'BPJS Health must be a number',
            'bpjs_kesehatan.min' => 'BPJS Health cannot be negative',
            'bpjs_kesehatan.max' => 'BPJS Health cannot exceed 100%',
            'bpjs_tenagakerja.required' => 'BPJS Employment is required',
            'bpjs_tenagakerja.numeric' => 'BPJS Employment must be a number',
            'bpjs_tenagakerja.min' => 'BPJS Employment cannot be negative',
            'bpjs_tenagakerja.max' => 'BPJS Employment cannot exceed 100%',
            'tgl_masuk.required' => 'Join date is required',
            'tgl_masuk.date' => 'Join date must be a valid date format',
            'is_active.boolean' => 'Active status must be true or false'
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
