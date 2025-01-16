<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust authorization as needed
    }

    public function rules()
    {
        return [
            // Main Product Validation
            'name' => 'nullable|string|min:4',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|string', // Base64 image string
            'background_image' => 'nullable|string', // Base64 image string
            'images.*' => 'nullable|string', // Base64 image strings

            // Variants Validation
            'variants' => 'nullable|array', // Variants are optional for update
            'variants.*.id' => 'nullable|exists:products,id', // Variant must exist if updating
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
            'variants.*.selling_price' => 'nullable|numeric|min:0',
            'variants.*.material' => 'nullable|string|max:255',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.style' => 'nullable|string|max:255',
            'variants.*.color' => 'nullable|string|max:255',
            'variants.*.capacity' => 'nullable|string|max:255',
            'variants.*.stock' => 'nullable|integer|min:0',
            'variants.*.images.*' => 'nullable|string', 

            // Categories Validation
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'nullable|exists:product_categories,id',
        ];
    }

    public function messages()
    {
        return [
            // Main Product Messages
            'name.string' => 'The product name must be a string.',
            'description.string' => 'The product description must be a string.',
            'cover_image.string' => 'Cover image must be a valid base64 string.',
            'background_image.string' => 'Background image must be a valid base64 string.',
            
            // Variants Messages
            'variants.array' => 'Variants should be an array.',
            'variants.*.id.exists' => 'One or more variant IDs are invalid.',
            'variants.*.stock.integer' => 'Variant stock must be an integer.',
            'variants.*.selling_price.numeric' => 'Variant selling price must be a valid number.',
            'variants.*.selling_price.min' => 'Variant selling price must be at least 0.',
            'variants.*.stock_number.string' => 'Variant stock number must be a string.',
            'variants.*.barcode.string' => 'Variant barcode must be a string.',
            'variants.*.qr_code.string' => 'Variant QR code must be a string.',
            'variants.*.serial_number.string' => 'Variant serial number must be a string.',
            'variants.*.size.string' => 'Variant size must be a string.',
            'variants.*.gender.in' => 'Variant gender must be one of the following: Male, Female, Unisex.',
            'variants.*.start_date.date' => 'Variant start date must be a valid date.',
            'variants.*.end_date.date' => 'Variant end date must be a valid date.',
            'variants.*.base_price.numeric' => 'Variant base price must be a valid number.',
            'variants.*.selling_price.required' => 'Variant selling price is required.',
            'variants.*.stock.required' => 'Variant stock is required.',
            'variants.*.images.*.string' => 'Variant images must be valid base64 strings.',

            // Category Messages
            'category_ids.*.exists' => 'One or more selected categories are invalid.',
        ];
    }
}
