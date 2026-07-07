<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TransaksiOperasionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('POST');
        $transaksiId = $this->route('id');

        // get jenis_transaksi from request data
        $jenisTransaksi = $this->jenis_transaksi ?? null;

        $rules = [
            'tanggal' => [$isCreate ? 'required' : 'sometimes', 'date'],
            'jenis_transaksi' => [
                $isCreate ? 'required' : 'sometimes',
                Rule::in(['PEMBELIAN_GAS', 'MAINTENANCE', 'PENJUALAN_GAS', 'LAINNYA']),
            ],
            'keterangan' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:500'],
            'is_pemasukan' => [$isCreate ? 'required' : 'sometimes', 'boolean'],
            'sumber_kas_id' => [$isCreate ? 'required' : 'sometimes', 'string', 'exists:tb_sumber_kas,id'],
            'created_by' => ['nullable', 'string'],
        ];

        // conditional rules based on jenis_transaksi
        switch ($jenisTransaksi) {
            case 'PENJUALAN_GAS':
                // jual ke pangkalan: butuh pangkalan, tabung, qty, harga
                $rules['pangkalan_id'] = ['required', 'string', 'exists:tb_pangkalan,id'];
                $rules['tabung_id'] = ['required', 'string', 'exists:tb_tabung,id'];
                $rules['qty'] = ['required', 'integer', 'min:1'];
                $rules['unit'] = ['required', 'string', 'max:20'];
                $rules['harga_satuan'] = ['required', 'numeric', 'min:0.01'];
                $rules['jumlah'] = ['sometimes', 'numeric', 'min:0.01']; 
                break;

            case 'PEMBELIAN_GAS':
                // beli dari supplier: butuh tabung, qty, harga (TIDAK butuh pangkalan)
                $rules['tabung_id'] = ['required', 'string', 'exists:tb_tabung,id'];
                $rules['qty'] = ['required', 'integer', 'min:1'];
                $rules['unit'] = ['required', 'string', 'max:20'];
                $rules['harga_satuan'] = ['required', 'numeric', 'min:0.01'];
                $rules['jumlah'] = ['sometimes', 'numeric', 'min:0.01'];
                $rules['pangkalan_id'] = ['nullable', 'string', 'exists:tb_pangkalan,id']; 
                break;

            case 'MAINTENANCE':
                // maintenance: butuh jumlah, asset_id optional karena maintenance tidak selalu berkaitan dengan asset
                $rules['jumlah'] = ['required', 'numeric', 'min:0.01'];
                $rules['asset_id'] = ['nullable', 'string', 'exists:tb_asset,id']; 
                break;

            case 'LAINNYA':
                // transaksi lainnya: butuh jumlah saja
                $rules['jumlah'] = ['required', 'numeric', 'min:0.01'];
                break;
        }

        // kondisi untuk update dan create untuk no_ref apabila create maka unique, apabila update maka where id != $transaksiId untuk menghindari duplikasi data 
        if ($isCreate) {
            $rules['no_ref'] = ['sometimes', 'string', 'max:50', 'unique:tb_transaksi_operasional,no_ref'];
        } else {
            $rules['no_ref'] = [
                'sometimes',
                'string',
                'max:50',
                "unique:tb_transaksi_operasional,no_ref,{$transaksiId}",
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal transaksi wajib diisi',
            'tanggal.date' => 'Format tanggal tidak valid',
            'jenis_transaksi.required' => 'Jenis transaksi wajib dipilih',
            'jenis_transaksi.in' => 'Jenis transaksi tidak valid',
            'keterangan.required' => 'Keterangan wajib diisi',
            'keterangan.max' => 'Keterangan maksimal 500 karakter',
            'is_pemasukan.required' => 'Status transaksi wajib dipilih',
            'is_pemasukan.boolean' => 'Status transaksi tidak valid',
            'jumlah.required' => 'Jumlah transaksi wajib diisi',
            'jumlah.numeric' => 'Jumlah harus berupa angka',
            'jumlah.min' => 'Jumlah minimal 0.01',
            'sumber_kas_id.required' => 'Sumber kas wajib dipilih',
            'sumber_kas_id.exists' => 'Sumber kas tidak ditemukan',
            'pangkalan_id.required' => 'Pangkalan wajib dipilih untuk penjualan',
            'pangkalan_id.exists' => 'Pangkalan tidak ditemukan',
            'tabung_id.required' => 'Tabung wajib dipilih untuk transaksi gas',
            'tabung_id.exists' => 'Tabung tidak ditemukan',
            'qty.required' => 'Quantity wajib diisi untuk transaksi gas',
            'qty.integer' => 'Quantity harus bilangan bulat',
            'qty.min' => 'Quantity minimal 1',
            'unit.required' => 'Unit wajib diisi untuk transaksi gas',
            'unit.max' => 'Unit maksimal 20 karakter',
            'harga_satuan.required' => 'Harga satuan wajib diisi untuk transaksi gas',
            'harga_satuan.numeric' => 'Harga satuan harus berupa angka',
            'harga_satuan.min' => 'Harga satuan minimal 0.01',
            'no_ref.unique' => 'Nomor referensi sudah digunakan',
            'no_ref.max' => 'Nomor referensi maksimal 50 karakter',
        ];
    }

    protected function prepareForValidation()
    {
        // auto-generate no_ref apabila kosong dan create
        if ($this->isMethod('POST') && empty($this->no_ref)) {
            $prefix = $this->boolean('is_pemasukan', false) ? 'IN' : 'OUT';
            $date = date('Ymd');
            $random = strtoupper(\Illuminate\Support\Str::random(6));
            $this->merge([
                'no_ref' => "TRX-{$prefix}-{$date}-{$random}",
            ]);
        }

        // set created_by dari auth user
        if (empty($this->created_by)) {
            $this->merge([
                'created_by' => Auth::user()->name ?? 'system',
            ]);
        }
        $requestData = $this->all();

        if (isset($requestData['jenis_transaksi']) &&
            in_array($requestData['jenis_transaksi'], ['PEMBELIAN_GAS', 'PENJUALAN_GAS'])) {

            if (isset($requestData['qty']) && isset($requestData['harga_satuan'])) {
                $qty = (float) $requestData['qty'];
                $hargaSatuan = (float) $requestData['harga_satuan'];

                $this->merge([
                    'jumlah' => $qty * $hargaSatuan,
                ]);
            }
        }
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
