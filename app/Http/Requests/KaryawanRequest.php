<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
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
            'alamat' => ['nullable', 'string'],
        ];

        // Untuk update, tambahkan unique rule
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $karyawanId = $this->route('id');

            $rules['nik'] = [
                'sometimes',
                'regex:/^[0-9]{16}$/',
                'digits:16',
                'unique:tb_karyawan,nik,'.$karyawanId,
            ];
        }
        // Untuk create, tambahkan unique rule
        else {
            $rules['nik'] = [
                'required',
                'regex:/^[0-9]{16}$/',
                'digits:16',
                'unique:tb_karyawan,nik',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi',
            'nik.unique' => 'NIK sudah terdaftar',
            'nik.regex' => 'NIK harus berupa angka',
            'nik.digits' => 'NIK harus terdiri dari 16 digit',
            'nama.required' => 'Nama wajib diisi',
            'nama.max' => 'Nama tidak boleh lebih dari 255 karakter',
            'jabatan.required' => 'Jabatan wajib diisi',
            'jabatan.max' => 'Jabatan tidak boleh lebih dari 100 karakter',
            'gaji_pokok.required' => 'Gaji pokok wajib diisi',
            'gaji_pokok.numeric' => 'Gaji pokok harus berupa angka',
            'gaji_pokok.min' => 'Gaji pokok tidak boleh bernilai negatif',
            'bpjs_kesehatan.max' => 'Persentase BPJS Kesehatan tidak boleh melebihi 100%',
            'bpjs_tenagakerja.max' => 'Persentase BPJS Ketenagakerjaan tidak boleh melebihi 100%',
            'tgl_masuk.required' => 'Tanggal masuk wajib diisi',
            'tgl_masuk.date' => 'Tanggal masuk harus dalam format tanggal yang valid',
            'aktif.boolean' => 'Status aktif harus bernilai true atau false',
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
