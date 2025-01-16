<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
class UpdateUserRequest extends FormRequest
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
            'name' => 'string|max:255',
            'email' => 'string|email|max:255',
            'password' => 'string|min:8',
            'gender' => 'string|max:255',
            'date_of_birth' => 'date',
            'phone' => 'string|max:255',
            'role_id' => 'exists:roles,id',
        ];
    }
}
