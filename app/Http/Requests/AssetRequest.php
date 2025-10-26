<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'identity' => 'required|string|max:100',
            'qty' => 'required|integer|min:0',
            'note' => 'nullable|string|max:500',
            'its_rfu' => 'required|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name required',
            'identity.required' => 'Identity required',
            'identity.unique' => 'Identity already exists please use another identity',
            'qty.required' => 'Quantity required',
            'qty.min' => 'Quantity cannot be negative',
        ];
    }
}