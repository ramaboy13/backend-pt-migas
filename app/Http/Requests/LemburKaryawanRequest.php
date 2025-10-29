<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
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
      'keterangan' => ['nullable', 'string', 'max:500']
    ];
  }

  public function messages(): array
  {
    return [
      'tanggal.required' => 'Overtime date is required',
      'tanggal.date' => 'Invalid date format',
      'hari.required' => 'Day is required',
      'hari.max' => 'Day may not exceed 20 characters',
      'karyawan_id.required' => 'Employee must be selected',
      'karyawan_id.exists' => 'Employee not found',
      'jam_lembur.required' => 'Overtime hours are required',
      'jam_lembur.numeric' => 'Overtime hours must be a number',
      'jam_lembur.min' => 'Overtime hours must be at least 0.5 hour',
      'jam_lembur.max' => 'Overtime hours may not exceed 12 hours',
      'keterangan.max' => 'Description may not exceed 500 characters'
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
