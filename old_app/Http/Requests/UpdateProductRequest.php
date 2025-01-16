<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'nullable|string|min:4',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'base_price' => 'numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'track_stock' => 'nullable|in:1,0',
            'track_stock_number' => 'nullable|string',
            'rating' => 'numeric|min:0',
            'barcode' => 'nullable|string',
            'qr_code' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'parent_id' => 'nullable|exists:products,id',
            'color' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:255',
            'material' => 'nullable|string|max:255',
            'style' => 'nullable|string|max:255',
            'capacity' => 'nullable|string|max:255',
            'weight' => 'nullable|string|max:255',
            'base_price' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'background_image' => 'nullable|string',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json($validator->errors(), 422));
    }
}
