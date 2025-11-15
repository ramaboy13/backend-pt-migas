<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $isCreate = $this->isMethod('POST');
    $userId = $this->route('id');

    $rules = [
      'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:255'],
      'email' => [$isCreate ? 'required' : 'sometimes', 'email', 'max:255'],
      'is_active' => ['sometimes', 'boolean'],
      'roles' => [$isCreate ? 'required' : 'sometimes', 'array'],
      'roles.*' => ['string', 'in:super_admin,admin']
    ];

    // Password rules
    if ($isCreate) {
      $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
      $rules['password_confirmation'] = ['required', 'string', 'min:8'];
      $rules['email'][] = 'unique:users,email';
    } else {
      $rules['password'] = ['sometimes', 'string', 'min:8', 'confirmed'];
      $rules['password_confirmation'] = ['sometimes', 'string', 'min:8'];
      $rules['email'][] = 'unique:users,email,' . $userId;
    }

    return $rules;
  }

  public function messages(): array
  {
    return [
      'name.required' => 'Nama user harus diisi',
      'name.max' => 'Nama user maksimal 255 karakter',
      'email.required' => 'Email harus diisi',
      'email.email' => 'Format email tidak valid',
      'email.unique' => 'Email sudah terdaftar',
      'email.max' => 'Email maksimal 255 karakter',
      'password.required' => 'Password harus diisi',
      'password.min' => 'Password minimal 8 karakter',
      'password.confirmed' => 'Konfirmasi password tidak cocok',
      'password_confirmation.required' => 'Konfirmasi password harus diisi',
      'is_active.boolean' => 'Status aktif harus true atau false',
      'roles.required' => 'Role harus dipilih',
      'roles.array' => 'Role harus berupa array',
      'roles.*.in' => 'Role harus super_admin atau admin'
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
