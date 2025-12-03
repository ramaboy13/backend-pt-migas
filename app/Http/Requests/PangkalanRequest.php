<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class PangkalanRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $isCreate = $this->isMethod('POST');
    $pangkalanId = $this->route('id');

    return [
      'regist_id' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'digits:10', Rule::unique('tb_pangkalan', 'regist_id')->ignore($pangkalanId)],
      'nama' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:255'],
      'no_ktp' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'digits:16', Rule::unique('tb_pangkalan', 'no_ktp')->ignore($pangkalanId)],
      'alamat' => ['nullable', 'string'],
      'harga_satuan' => ['nullable', 'numeric', 'min:0'],
    ];
  }

  public function messages(): array
  {
    return [
      'regist_id.required' => 'Registration ID is required',
      'regist_id.unique' => 'Registration ID already exists',
      'regist_id.numeric' => 'Registration ID must be a number',
      'regist_id.digits' => 'Registration ID must be 10 digits',
      'nama.required' => 'nama is required',
      'nama.max' => 'nama must not exceed 255 characters',
      'no_ktp.required' => 'KTP number is required',
      'no_ktp.unique' => 'KTP number already exists',
      'no_ktp.numeric' => 'KTP number must be a number',
      'no_ktp.digits' => 'KTP number must be 16 digits',
      'harga_satuan.numeric' => 'Unit price must be a number',
      'harga_satuan.min' => 'Unit price cannot be negative',
    ];
  }

  protected function failedValidation(Validator $validator)
  {
    throw new HttpResponseException(
      response()->json([
        'success' => false,
        'message' => 'Validation failed',
        'data' => null,
        'errors' => $validator->errors()
      ], 422)
    );
  }
}
