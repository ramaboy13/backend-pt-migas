<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class KasBcaRequest extends FormRequest
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
            'keterangan' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:255'],
            'debit' => ['required', 'numeric', 'min:0'],
            'kredit' => ['required', 'numeric', 'min:0'],
            'saldo' => ['sometimes', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Date is required',
            'tanggal.date' => 'Invalid date format',
            'keterangan.required' => 'Description is required',
            'keterangan.max' => 'Description must not exceed 255 characters',
            'debit.required' => 'Debit amount is required',
            'debit.numeric' => 'Debit must be a numeric value',
            'debit.min' => 'Debit cannot be negative',
            'kredit.required' => 'Credit amount is required',
            'kredit.numeric' => 'Credit must be a numeric value',
            'kredit.min' => 'Credit cannot be negative',
        ];
    }

    protected function prepareForValidation()
    {
        // Convert null values to 0 for debit and credit
        $this->merge([
            'debit' => $this->debit ?? 0,
            'kredit' => $this->kredit ?? 0,
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $debit = (float) $this->input('debit', 0);
            $kredit = (float) $this->input('kredit', 0);

             //  minimal salah satu harus ada nilai (hanya untuk create)
            if ($debit > 0 && $kredit > 0) {
                $validator->errors()->add('debit', 'You cannot fill in both debit and credit at the same time');
                $validator->errors()->add('kredit', 'You cannot fill in both debit and credit at the same time');
            }

              // tidak boleh kedua-duanya lebih dari 0
            if ($this->isMethod('POST') && $debit == 0 && $kredit == 0) {
                $validator->errors()->add('debit', 'You must fill in at least one: debit or credit');
                $validator->errors()->add('kredit', 'You must fill in at least one: debit or credit');
            }
        });
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

