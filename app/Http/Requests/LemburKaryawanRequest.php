<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LemburKaryawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('POST');

        return [
            'tanggal' => [$isCreate ? 'required' : 'sometimes', 'date'],
            'hari' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:20'],
            'karyawan_id' => [$isCreate ? 'required' : 'sometimes', 'string', 'exists:tb_karyawan,id'],
            'jam_lembur' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'min:0.5', 'max:12'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal lembur wajib diisi',
            'tanggal.date' => 'Format tanggal tidak valid',
            'hari.required' => 'Hari wajib diisi',
            'hari.max' => 'Hari tidak boleh lebih dari 20 karakter',
            'karyawan_id.required' => 'Karyawan wajib dipilih',
            'karyawan_id.exists' => 'Data karyawan tidak ditemukan',
            'jam_lembur.required' => 'Jumlah jam lembur wajib diisi',
            'jam_lembur.numeric' => 'Jumlah jam lembur harus berupa angka',
            'jam_lembur.min' => 'Jumlah jam lembur minimal 0,5 jam',
            'jam_lembur.max' => 'Jumlah jam lembur tidak boleh lebih dari 12 jam',
            'keterangan.max' => 'Keterangan tidak boleh lebih dari 500 karakter',
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
