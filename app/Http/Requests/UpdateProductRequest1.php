<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest1 extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust authorization as needed
    }

    public function rules()
    {
        return [
            // Main Product Validation
            'name' => 'required|string|min:4',
            'description' => 'required|string',
            'cover_image' => 'nullable|file|image|max:2048', // Max 2MB
            'background_image' => 'nullable|file|image|max:2048', // Max 2MB
            'images' => 'nullable|array',
            'images.*' => 'nullable|file|image|max:2048', // Multiple images, max 2MB each

            // Variants Validation
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'nullable|integer|exists:products,id',
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
            'variants.*.images.*' => 'nullable|file|image|max:2048', // Variant additional images
        ];
    }

    public function messages()
    {
        return [
            // Main Product Messages
            'name.required' => 'The product name is required.',
            'description.required' => 'The product description is required.',
            'cover_image.image' => 'The cover image must be an image file.',
            'background_image.image' => 'The background image must be an image file.',
            'images.*.image' => 'Each additional image must be an image file.',

            // Variants Messages
            'variants.required' => 'At least one variant is required.',
            'variants.*.selling_price.required' => 'The selling price is required for each variant.',
            'variants.*.stock.required' => 'The stock field is required for each variant.',
            'variants.*.background_image_file.image' => 'The variant background image must be an image file.',
            'variants.*.images.*.image' => 'Each variant image must be an image file.',
        ];
    }
}
