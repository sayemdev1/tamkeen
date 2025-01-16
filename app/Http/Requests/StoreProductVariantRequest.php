<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
class StoreProductVariantRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json($validator->errors(), 422));
    }

    public function rules()
    {
        return [
            'product_id' => 'required|exists:products,id',
            'track_stock' => 'boolean',
            'stock_number' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'qr_code' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'size' => 'nullable|in:XS,S,M,L,XL,XXL',
            'gender' => 'nullable|in:Male,Female,Unisex',
            'discount' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'base_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'material' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'style' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'capacity' => 'nullable|string|max:255',
            'stock' => 'nullable|integer|min:0',
            'background_image_path' => 'nullable|string|max:255',
        ];
    }
}
