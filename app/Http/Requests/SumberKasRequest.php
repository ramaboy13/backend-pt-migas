<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class SumberKasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('POST');
        $sumberKasId = $this->route('id');

        $rules = [
            'tipe' => [$isCreate ? 'required' : 'sometimes', Rule::in(['BANK', 'CASH'])],
            'nama_bank' => ['nullable', 'string', 'max:100'],
            'nomor_rekening' => ['nullable', 'string', 'max:50'],
            'atas_nama' => ['nullable', 'string', 'max:100'],
            'saldo_awal' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'aktif' => ['sometimes', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ];

        // validasi untuk tipe bank
        if ($this->input('tipe') === 'BANK' || ($isCreate && ! $this->has('tipe'))) {
            $rules['nama_bank'] = ['required', 'string', 'max:100'];
            $rules['nomor_rekening'] = ['required', 'string', 'max:50'];
            $rules['atas_nama'] = ['required', 'string', 'max:100'];
        }

        // validasi nomor rekening untuk tipe bank
        if ($isCreate) {
            $rules['nomor_rekening'] = array_merge(
                $rules['nomor_rekening'] ?? ['nullable'],
                ['unique:tb_sumber_kas,nomor_rekening,NULL,id,tipe,BANK']
            );
        } else {
            $rules['nomor_rekening'] = array_merge(
                $rules['nomor_rekening'] ?? ['nullable'],
                ["unique:tb_sumber_kas,nomor_rekening,{$sumberKasId},id,tipe,BANK"]
            );
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'tipe.required' => 'Tipe sumber kas wajib dipilih',
            'tipe.in' => 'Tipe sumber kas harus BANK atau CASH',
            'nama_bank.required' => 'Nama bank wajib diisi untuk tipe BANK',
            'nama_bank.max' => 'Nama bank maksimal 100 karakter',
            'nomor_rekening.required' => 'Nomor rekening wajib diisi untuk tipe BANK',
            'nomor_rekening.unique' => 'Nomor rekening sudah terdaftar',
            'nomor_rekening.max' => 'Nomor rekening maksimal 50 karakter',
            'atas_nama.required' => 'Atas nama wajib diisi untuk tipe BANK',
            'atas_nama.max' => 'Atas nama maksimal 100 karakter',
            'saldo_awal.required' => 'Saldo awal wajib diisi',
            'saldo_awal.numeric' => 'Saldo awal harus berupa angka',
            'saldo_awal.min' => 'Saldo awal tidak boleh negatif',
            'keterangan.max' => 'Keterangan maksimal 500 karakter',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->isMethod('POST')) {
            $this->merge([
                'aktif' => $this->has('aktif') ? $this->boolean('aktif') : true,
                'saldo_awal' => $this->has('saldo_awal') ? $this->input('saldo_awal') : 0,
            ]);
        } elseif ($this->has('aktif')) {
            $this->merge([
                'aktif' => $this->boolean('aktif'),
            ]);
        }

        // untuk tipe cash, clear bank related
        if ($this->input('tipe') === 'CASH') {
            $this->merge([
                'nama_bank' => null,
                'nomor_rekening' => null,
                'atas_nama' => null,
            ]);
        }
    }

    public function attributes(): array
    {
        return [
            'nama_bank' => 'nama bank',
            'nomor_rekening' => 'nomor rekening',
            'atas_nama' => 'atas nama',
            'saldo_awal' => 'saldo awal',
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
