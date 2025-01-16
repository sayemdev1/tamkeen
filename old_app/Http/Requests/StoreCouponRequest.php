<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
class StoreCouponRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'coupon_type' => 'required|in:discount,cashback,giftcard',
            'promotion_code' => 'required|string|max:255|unique:coupons,promotion_code,',
            'expired_at' => 'required|date|after:today',
            'discount_type' => 'required|in:percentage,fixed',
            'percentage' => 'nullable|numeric|min:0|max:100|required_if:discount_type,percentage',
            'status' => 'required|in:active,inactive,expired,used',
            'number_of_uses' => 'required|integer|min:1',
            'use_for' => 'required|in:product,package,basket,order',
        ];
    }
}
