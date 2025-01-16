<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust according to your needs
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json($validator->errors(), 422));
    }

    public function rules()
    {
        return [
            'name' => 'required|string|min:4',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'base_price' => 'numeric|min:0',
            'stock' => 'required',
            'track_stock' => 'required|in:1,0',
            'track_stock_number' => 'nullable',
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
            'capacity' => 'nullable',
            'weight' => 'nullable|string|max:255',
            'base_price' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'background_image' => 'nullable|string',
            
        ];
    }


}
