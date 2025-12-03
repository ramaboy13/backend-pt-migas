<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransaksiOperasionalRequest extends FormRequest
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
      'no_ref' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:50'],
      'keterangan' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:255'],
      'pangkalan_id' => [$isCreate ? 'required' : 'sometimes', 'string', 'exists:tb_pangkalan,id'],
      'tabung_id' => [$isCreate ? 'required' : 'sometimes', 'string', 'exists:tb_tabung,id'],
      'is_in' => [$isCreate ? 'required' : 'sometimes', 'boolean'],
      'qty' => [$isCreate ? 'required' : 'sometimes', 'integer', 'min:1'],
      'unit' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:20'],
      'debit' => ['sometimes', 'numeric', 'min:0'],
      'credit' => ['sometimes', 'numeric', 'min:0']
    ];

    // Untuk create, tambahkan unique rule untuk no_ref
    if ($isCreate) {
      $rules['no_ref'][] = 'unique:tb_transaksi_operasional,no_ref';
    } else {
      // Untuk update, ignore current record
      $transaksiId = $this->route('id');
      $rules['no_ref'] = [
        'sometimes',
        'string',
        'max:50',
        'unique:tb_transaksi_operasional,no_ref,' . $transaksiId
      ];
    }

    return $rules;
  }

  public function messages(): array
  {
    return [
      'tanggal.required' => 'Tanggal transaksi harus diisi',
      'tanggal.date' => 'Format tanggal tidak valid',
      'no_ref.required' => 'Nomor referensi harus diisi',
      'no_ref.unique' => 'Nomor referensi sudah digunakan',
      'no_ref.max' => 'Nomor referensi maksimal 50 karakter',
      'keterangan.required' => 'Keterangan harus diisi',
      'keterangan.max' => 'Keterangan maksimal 255 karakter',
      'pangkalan_id.required' => 'Pangkalan harus dipilih',
      'pangkalan_id.exists' => 'Pangkalan tidak ditemukan',
      'tabung_id.required' => 'Tabung harus dipilih',
      'tabung_id.exists' => 'Tabung tidak ditemukan',
      'is_in.required' => 'Jenis transaksi harus dipilih',
      'is_in.boolean' => 'Jenis transaksi tidak valid',
      'qty.required' => 'Quantity harus diisi',
      'qty.integer' => 'Quantity harus berupa angka bulat',
      'qty.min' => 'Quantity minimal 1',
      'unit.required' => 'Unit harus diisi',
      'unit.max' => 'Unit maksimal 20 karakter',
      'debit.numeric' => 'Debit harus berupa angka',
      'debit.min' => 'Debit tidak boleh negatif',
      'credit.numeric' => 'Credit harus berupa angka',
      'credit.min' => 'Credit tidak boleh negatif'
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
