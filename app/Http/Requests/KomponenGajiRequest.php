<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class KomponenGajiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('POST');

        $rules = [
            'tipe' => [$isCreate ? 'required' : 'sometimes', Rule::in(['LEMBUR', 'TUNJANGAN'])],
            'karyawan_id' => [$isCreate ? 'required' : 'sometimes', 'string', 'exists:tb_karyawan,id'],
            'tanggal' => [$isCreate ? 'required' : 'sometimes', 'date'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'nominal' => ['required_if:tipe,TUNJANGAN', 'nullable', 'numeric', 'min:0'],
        ];

        // Conditional validation untuk LEMBUR
        if ($this->input('tipe') === 'LEMBUR') {
            $rules['jam_lembur'] = [$isCreate ? 'required' : 'sometimes', 'numeric', 'min:0.5', 'max:12'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'tipe.required' => 'Tipe komponen wajib dipilih',
            'tipe.in' => 'Tipe komponen harus LEMBUR atau TUNJANGAN',
            'karyawan_id.required' => 'Karyawan wajib dipilih',
            'karyawan_id.exists' => 'Data karyawan tidak ditemukan',
            'tanggal.required' => 'Tanggal wajib diisi',
            'tanggal.date' => 'Format tanggal tidak valid',
            'jam_lembur.required' => 'Jumlah jam lembur wajib diisi',
            'jam_lembur.numeric' => 'Jumlah jam lembur harus berupa angka',
            'jam_lembur.min' => 'Jumlah jam lembur minimal 0,5 jam',
            'jam_lembur.max' => 'Jumlah jam lembur tidak boleh lebih dari 12 jam',
            'nominal.required_if' => 'Nominal tunjangan wajib diisi',
            'nominal.numeric' => 'Nominal harus berupa angka',
            'nominal.min' => 'Nominal tidak boleh negatif',
            'keterangan.max' => 'Keterangan tidak boleh lebih dari 500 karakter',
        ];
    }

    protected function prepareForValidation()
    {
        // Untuk tipe TUNJANGAN, set jam_lembur ke null
        if ($this->input('tipe') === 'TUNJANGAN') {
            $this->merge([
                'jam_lembur' => null,
            ]);
        }

        // Untuk tipe LEMBUR, pastikan nominal dihitung otomatis (akan di-override di service)
        if ($this->input('tipe') === 'LEMBUR') {
            $this->merge([
                'nominal' => 0, // Sementara, akan dihitung di service
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $karyawan = \App\Models\Karyawan::where('id', $this->karyawan_id)
                ->where('aktif', true)
                ->first();

            if (! $karyawan) {
                $validator->errors()->add(
                    'karyawan_id',
                    'Karyawan tidak ditemukan atau tidak aktif'
                );
            }

            // memastikan tanggal tidak sebelum tanggal masuk karyawan
            if ($this->has('karyawan_id') && $this->has('tanggal')) {
                $karyawan = \App\Models\Karyawan::find($this->karyawan_id);
                if ($karyawan && $karyawan->tgl_masuk) {
                    $tanggalKomponen = \Carbon\Carbon::parse($this->tanggal);
                    $tanggalMasuk = \Carbon\Carbon::parse($karyawan->tgl_masuk);

                    if ($tanggalKomponen->lt($tanggalMasuk)) {
                        $validator->errors()->add('tanggal', 'Tanggal tidak boleh sebelum tanggal masuk karyawan');
                    }
                }
            }
        });
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
