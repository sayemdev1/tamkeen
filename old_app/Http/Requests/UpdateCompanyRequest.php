<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'string|max:255',
            'email' => 'email|max:255|unique:users,email',
            'phone' => 'string|min:10|max:15',
            'address' => 'string|max:500',
            'logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',

        ];
    }
}
