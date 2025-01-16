<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreProductRequest1 extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust authorization as needed
    }

    /**
     * Handle failed validation.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json($validator->errors(), 422));
    }

    /**
     * Define the validation rules.
     */
    public function rules()
    {
        return [
            // Main Product Validation
            'name' => 'required|string|min:4',
            'description' => 'required|string',
            'cover_image' => 'nullable|file|image|max:2048', // File upload validation (image, max 2MB)
            'background_image' => 'nullable|file|image|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'nullable|file|image|max:2048', // Validate multiple file uploads

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
            'variants.*.background_image_file' => 'nullable|file|image|max:2048', // Variant background image
            'variants.*.images' => 'nullable|array',
            'variants.*.images.*' => 'nullable|file|image|max:2048', // Validate images for each variant
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages()
    {
        return [
            // Main Product Messages
            'name.required' => 'The product name is required.',
            'description.required' => 'The product description is required.',
            'cover_image.image' => 'The cover image must be a valid image file.',
            'background_image.image' => 'The background image must be a valid image file.',
            'images.*.image' => 'Each additional image must be a valid image file.',

            // Variants Messages
            'variants.required' => 'At least one variant is required.',
            'variants.*.selling_price.required' => 'The selling price is required for each variant.',
            'variants.*.stock.required' => 'The stock field is required for each variant.',
            'variants.*.background_image_file.image' => 'The background image for the variant must be a valid image file.',
            'variants.*.images.*.image' => 'Each image for the variant must be a valid image file.',
        ];
    }
}
