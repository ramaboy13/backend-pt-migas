<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class GajiKaryawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('POST');

        return [
            'karyawan_id' => [$isCreate ? 'required' : 'sometimes', 'string', 'exists:tb_karyawan,id'],
            'bulan' => [$isCreate ? 'required' : 'sometimes', 'integer', 'min:1', 'max:12'],
            'tahun' => [$isCreate ? 'required' : 'sometimes', 'integer', 'min:2020', 'max:2030'],
            'tanggal_gaji' => ['nullable', 'date'],
            'potongan_lainnya' => ['nullable', 'numeric', 'min:0'],
            'pph21' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in(['Belum Dibayar', 'Telah Dibayar'])],
        ];
    }

    public function messages(): array
    {
        return [
            'karyawan_id.required' => 'Karyawan wajib dipilih',
            'karyawan_id.exists' => 'Data karyawan tidak ditemukan',
            'bulan.required' => 'Bulan wajib diisi',
            'bulan.integer' => 'Bulan harus berupa angka',
            'bulan.min' => 'Bulan minimal 1',
            'bulan.max' => 'Bulan maksimal 12',
            'tahun.required' => 'Tahun wajib diisi',
            'tahun.integer' => 'Tahun harus berupa angka',
            'tahun.min' => 'Tahun minimal 2020',
            'tahun.max' => 'Tahun maksimal 2030',
            'tanggal_gaji.date' => 'Format tanggal gaji tidak valid',
            'potongan_lainnya.numeric' => 'Potongan lainnya harus berupa angka',
            'potongan_lainnya.min' => 'Potongan lainnya tidak boleh negatif',
            'pph21.numeric' => 'PPH21 harus berupa angka',
            'pph21.min' => 'PPH21 tidak boleh negatif',
            'status.in' => 'Status tidak valid',
        ];
    }

    protected function prepareForValidation()
    {
        // Set default nilai jika tidak diisi
        $this->merge([
            'potongan_lainnya' => $this->input('potongan_lainnya', 0),
            'pph21' => $this->input('pph21', 0),
            'status' => $this->input('status', 'Belum Dibayar'),
        ]);
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
