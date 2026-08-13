<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer_id' => 'nullable|integer|exists:customers,id',
            'amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'amount.required' => 'Please enter the amount paid by the customer.',
            'amount.numeric' => 'The paid amount must be a valid number.',
            'amount.min' => 'The paid amount cannot be negative.',
        ];
    }
}
