<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PendapatanRequest extends FormRequest
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
      'tunjangan' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'min:0'],
      'periode' => [$isCreate ? 'required' : 'sometimes', 'date', 'date_format:Y-m-d']
    ];

    // Untuk create, tambahkan unique constraint
    if ($isCreate) {
      $rules['periode'][] = 'unique:tb_pendapatan,periode,NULL,id,karyawan_id,' . $this->karyawan_id;
    } else {
      // Untuk update, ignore current record
      $pendapatanId = $this->route('id');
      $rules['periode'] = [
        'sometimes',
        'date',
        'date_format:Y-m-d',
        'unique:tb_pendapatan,periode,' . $pendapatanId . ',id,karyawan_id,' . $this->karyawan_id
      ];
    }

    return $rules;
  }

  public function messages(): array
  {
    return [
      'karyawan_id.required' => 'Karyawan harus dipilih',
      'karyawan_id.exists' => 'Karyawan tidak ditemukan',
      'tunjangan.required' => 'Tunjangan harus diisi',
      'tunjangan.numeric' => 'Tunjangan harus berupa angka',
      'tunjangan.min' => 'Tunjangan tidak boleh negatif',
      'periode.required' => 'Periode harus diisi',
      'periode.date' => 'Format periode tidak valid',
      'periode.date_format' => 'Format periode harus YYYY-MM-DD',
      'periode.unique' => 'Pendapatan untuk karyawan pada periode ini sudah ada'
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
