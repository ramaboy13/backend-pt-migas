<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GajiKaryawanRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $isCreate = $this->isMethod('POST');

    $rules = [
      'karyawan_id' => [$isCreate ? 'required' : 'sometimes', 'string', 'exists:tb_karyawan,id'],
      'pendapatan_id' => [$isCreate ? 'required' : 'sometimes', 'string', 'exists:tb_pendapatan,id'],
      'potongan_id' => [$isCreate ? 'required' : 'sometimes', 'string', 'exists:tb_potongan,id'],
      'pph21' => ['sometimes', 'numeric', 'min:0'],
      'periode' => [$isCreate ? 'required' : 'sometimes', 'date', 'date_format:Y-m-d']
    ];

    // Untuk create, tambahkan unique constraint
    if ($isCreate) {
      $rules['periode'][] = 'unique:tb_gaji_karyawan,periode,NULL,id,karyawan_id,' . $this->karyawan_id;
    } else {
      // Untuk update, ignore current record
      $gajiId = $this->route('id');
      $rules['periode'] = [
        'sometimes',
        'date',
        'date_format:Y-m-d',
        'unique:tb_gaji_karyawan,periode,' . $gajiId . ',id,karyawan_id,' . $this->karyawan_id
      ];
    }

    return $rules;
  }

  public function messages(): array
  {
    return [
      'karyawan_id.required' => 'Karyawan harus dipilih',
      'karyawan_id.exists' => 'Karyawan tidak ditemukan',
      'pendapatan_id.required' => 'Data pendapatan harus dipilih',
      'pendapatan_id.exists' => 'Data pendapatan tidak ditemukan',
      'potongan_id.required' => 'Data potongan harus dipilih',
      'potongan_id.exists' => 'Data potongan tidak ditemukan',
      'pph21.numeric' => 'PPH21 harus berupa angka',
      'pph21.min' => 'PPH21 tidak boleh negatif',
      'periode.required' => 'Periode harus diisi',
      'periode.date' => 'Format periode tidak valid',
      'periode.date_format' => 'Format periode harus YYYY-MM-DD',
      'periode.unique' => 'Gaji untuk karyawan pada periode ini sudah ada'
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
