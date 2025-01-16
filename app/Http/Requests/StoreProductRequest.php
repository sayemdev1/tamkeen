<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust authorization as needed
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json($validator->errors(), 422));
    }

    public function rules()
    {
        return [
            // Main Product Validation
            'name' => 'required|string|min:4',
            'description' => 'required|string',
            // 'base_price' => 'nullable|numeric|min:0',
            // 'selling_price' => 'nullable|numeric|min:0',
            // 'stock' => 'required|integer|min:0',
            // 'track_stock' => 'required|boolean',
            'cover_image' => 'nullable|string', // Base64 image string
            'background_image' => 'nullable|string', // Base64 image string
            'images' => 'nullable|array',
            'images.*' => 'nullable|string', // Base64 image strings

            // Variants Validation
            'variants' => 'required|array|min:1', // At least one variant is required
            'variants.*.track_stock' => 'nullable|boolean',
            'variants.*.stock_number' => 'nullable|string|max:255',
            'variants.*.barcode' => 'nullable|string|max:255',
            'variants.*.qr_code' => 'nullable|string|max:255',
            'variants.*.serial_number' => 'nullable|string|max:255',
            'variants.*.size' => 'nullable|string|max:255',
            'variants.*.gender' => 'nullable|string|in:Male,Female,Unisex',
            'variants.*.discount' => 'nullable|boolean',
            'variants.*.start_date' => 'nullable|date',
            'variants.*.end_date' => 'nullable|date',
            'variants.*.base_price' => 'nullable|numeric|min:0',
            'variants.*.selling_price' => 'required|numeric|min:0',
            'variants.*.material' => 'nullable|string|max:255',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.style' => 'nullable|string|max:255',
            'variants.*.color' => 'nullable|string|max:255',
            'variants.*.capacity' => 'nullable|string|max:255',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.images.*' => 'nullable|string', 
        ];
    }

    public function messages()
    {
        return [
            // Main Product Messages
            'name.required' => 'The product name is required.',
            'description.required' => 'The product description is required.',
            'stock.required' => 'The product stock is required.',
            'track_stock.required' => 'Tracking stock is required.',
            'category_ids.*.exists' => 'One or more selected categories are invalid.',

            // Variants Messages
            'variants.required' => 'At least one variant is required.',
            'variants.*.stock.required' => 'The stock field is required for each variant.',
            'variants.*.selling_price.required' => 'The selling price is required for each variant.',
        ];
    }
}


