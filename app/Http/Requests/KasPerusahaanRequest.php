<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class KasPerusahaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('POST');

        $rules = [
            'tanggal' => [$isCreate ? 'required' : 'sometimes', 'date'],
            'sumber_kas_id' => [$isCreate ? 'required' : 'sometimes', 'string', 'exists:tb_sumber_kas,id'],
            'keterangan' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:500'],
            'tipe_transaksi' => [$isCreate ? 'required' : 'sometimes', Rule::in(['DEBIT', 'KREDIT'])],
            'jumlah' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'min:0.01'],
            'transaksi_operasional_id' => ['nullable', 'string', 'exists:tb_transaksi_operasional,id'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal transaksi wajib diisi',
            'tanggal.date' => 'Format tanggal tidak valid',
            'sumber_kas_id.required' => 'Sumber kas wajib dipilih',
            'sumber_kas_id.exists' => 'Sumber kas tidak ditemukan',
            'keterangan.required' => 'Keterangan transaksi wajib diisi',
            'keterangan.max' => 'Keterangan maksimal 500 karakter',
            'tipe_transaksi.required' => 'Tipe transaksi wajib dipilih',
            'tipe_transaksi.in' => 'Tipe transaksi harus DEBIT atau KREDIT',
            'jumlah.required' => 'Jumlah transaksi wajib diisi',
            'jumlah.numeric' => 'Jumlah harus berupa angka',
            'jumlah.min' => 'Jumlah minimal 0.01',
            'transaksi_operasional_id.exists' => 'Transaksi operasional tidak ditemukan',
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
