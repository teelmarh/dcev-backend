<?php

namespace App\Http\Requests\User\Auth;

use Illuminate\Foundation\Http\FormRequest;

class registerUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'email' => 'required|email|unique:users,email',
            'state_chapter' => 'required|string',
            'dob' => 'required|date',
            'state_of_origin' => 'required|string',
            'lga' => 'required|string',
            'ward' => 'required|string',
            'gender' => 'nullable|string',
            'occupation' => 'required|string',
            'password' => 'nullable|min:8|confirmed',
           'image' => 'nullable|base64image|base64max:5120',
           'image_url' => 'nullable|string',
            'phone' => 'nullable|string|unique:users,phone',
            'disabled' => 'boolean',
        ];
    }
}
