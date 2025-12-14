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

        $rules = [
            'tanggal' => [$isCreate ? 'required' : 'sometimes', 'date'],
            'jenis_transaksi' => [
                $isCreate ? 'required' : 'sometimes',
                Rule::in(['PEMBELIAN_GAS', 'MAINTENANCE', 'PENJUALAN_PANGKALAN', 'LAINNYA']),
            ],
            'keterangan' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:500'],
            'is_pemasukan' => [$isCreate ? 'required' : 'sometimes', 'boolean'],
            'jumlah' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'min:0.01'],
            'sumber_kas_id' => [$isCreate ? 'required' : 'sometimes', 'string', 'exists:tb_sumber_kas,id'],
            'created_by' => ['nullable', 'string'],
        ];

        // Conditional rules based on jenis_transaksi
        switch ($this->input('jenis_transaksi')) {
            case 'PEMBELIAN_GAS':
            case 'PENJUALAN_PANGKALAN':
                $rules['pangkalan_id'] = ['required', 'string', 'exists:tb_pangkalan,id'];
                $rules['tabung_id'] = ['required', 'string', 'exists:tb_tabung,id'];
                $rules['qty'] = ['required', 'integer', 'min:1'];
                $rules['unit'] = ['required', 'string', 'max:20'];
                $rules['harga_satuan'] = ['required', 'numeric', 'min:0.01'];
                break;

            case 'MAINTENANCE':
                $rules['asset_id'] = ['required', 'string', 'exists:tb_assets,id'];
                break;

            case 'LAINNYA':
                $rules['keterangan'] = ['required', 'string', 'max:500'];
                break;
        }

        // For update, ignore current record for no_ref
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
            'pangkalan_id.required' => 'Pangkalan wajib dipilih',
            'pangkalan_id.exists' => 'Pangkalan tidak ditemukan',
            'tabung_id.required' => 'Tabung wajib dipilih',
            'tabung_id.exists' => 'Tabung tidak ditemukan',
            'qty.required' => 'Quantity wajib diisi',
            'qty.integer' => 'Quantity harus bilangan bulat',
            'qty.min' => 'Quantity minimal 1',
            'unit.required' => 'Unit wajib diisi',
            'unit.max' => 'Unit maksimal 20 karakter',
            'harga_satuan.required' => 'Harga satuan wajib diisi',
            'harga_satuan.numeric' => 'Harga satuan harus berupa angka',
            'harga_satuan.min' => 'Harga satuan minimal 0.01',
            'asset_id.required' => 'Asset wajib dipilih',
            'asset_id.exists' => 'Asset tidak ditemukan',
            'no_ref.unique' => 'Nomor referensi sudah digunakan',
            'no_ref.max' => 'Nomor referensi maksimal 50 karakter',
        ];
    }

    protected function prepareForValidation()
    {
        // Auto-generate no_ref jika kosong dan create
        if ($this->isMethod('POST') && empty($this->no_ref)) {
            $prefix = $this->is_pemasukan ? 'IN' : 'OUT';
            $date = date('Ymd');
            $random = strtoupper(\Illuminate\Support\Str::random(6));
            $this->merge([
                'no_ref' => "TRX-{$prefix}-{$date}-{$random}",
            ]);
        }

        // Set created_by dari auth user
        if (empty($this->created_by)) {
            $this->merge([
                'created_by' => Auth::user()->name ?? 'system',
            ]);
        }

        // Calculate jumlah if qty and harga_satuan provided
        if ($this->has('qty') && $this->has('harga_satuan')) {
            $qty = (float) $this->input('qty', 0);
            $hargaSatuan = (float) $this->input('harga_satuan', 0);
            $this->merge([
                'jumlah' => $qty * $hargaSatuan,
            ]);
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
