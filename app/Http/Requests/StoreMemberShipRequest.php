<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
class StoreMemberShipRequest extends FormRequest
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

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json($validator->errors(), 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
   public function rules()
    {
        return [
            'level_name' => 'required|string|max:255',
            'monthly_fee' => 'required|numeric',
            'description' => 'required|string|max:500',
            'condition_1' => 'string|max:255',
            'condition_2' => 'string|max:255',
            'percentage_in_level_1' => 'numeric',
            'percentage_in_level_2' => 'numeric',
            'percentage_in_level_3' => 'numeric',
            'color' => 'nullable|string|max:255',
            'icon' => 'required|image|mimes:jpeg,png,gif|max:20480', // Image validation for only images
        ];
    }

}
