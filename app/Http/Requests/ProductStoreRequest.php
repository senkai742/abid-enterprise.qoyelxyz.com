<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image',
            'barcode' => 'nullable|string|max:50|unique:products',
            'purchase_price' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
            'sell_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'status' => 'required|boolean',
        ];

        if (auth()->user() && auth()->user()->role === 'admin') {
            $rules['branch_stock'] = 'required|array|min:1';
            $rules['branch_stock.*'] = 'required|integer|min:0';
        } else {
            $rules['quantity'] = 'required|integer|min:0';
        }

        return $rules;
    }
}
