<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('POST');

        $rules = [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:255'],
            'identity' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:100'],
            'qty' => [$isCreate ? 'required' : 'sometimes', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
            'its_rfu' => [$isCreate ? 'required' : 'sometimes', 'boolean']
        ];

        // Untuk update, tambahkan unique rule dengan ignore
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $assetId = $this->route('id');
            $rules['identity'] = ['sometimes', 'string', 'max:100', 'unique:tb_asset,identity,' . $assetId];
        } else {
            // Untuk create, tambahkan unique rule
            $rules['identity'] = ['required', 'string', 'max:100', 'unique:tb_asset,identity'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Asset name is required',
            'name.max' => 'Asset name may not exceed 255 characters',
            'identity.required' => 'Asset identity is required',
            'identity.unique' => 'Asset identity has already been registered',
            'identity.max' => 'Asset identity may not exceed 100 characters',
            'qty.required' => 'Quantity is required',
            'qty.integer' => 'Quantity must be an integer',
            'qty.min' => 'Quantity cannot be negative',
            'note.max' => 'Note may not exceed 500 characters',
            'its_rfu.required' => 'RFU status is required',
            'its_rfu.boolean' => 'RFU status must be true or false'
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
