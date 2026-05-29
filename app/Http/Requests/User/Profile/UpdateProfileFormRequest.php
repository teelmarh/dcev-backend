<?php

namespace App\Http\Requests\User\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileFormRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'nullable|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email,'.$this->user()->id,
            'state_chapter' => 'nullable|string',
            'dob' => 'nullable|date',
            'state_of_origin' => 'nullable|string',
            'lga' => 'nullable|string',
            'ward' => 'nullable|string',
            'gender' => 'nullable|string',
            'occupation' => 'nullable|string',
            'password' => 'nullable|min:8|confirmed',
            'image_url' => 'nullable|string',
           'image' => 'nullable|base64image|base64max:5120',
            'phone' => 'nullable|string|unique:users,phone,'.$this->user()->id,
            'disabled' => 'boolean',
        ];
    }

    public function body(): array
    {
        return [

        ];
    }
}
