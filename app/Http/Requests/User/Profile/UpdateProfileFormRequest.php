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
            'email'      => 'nullable|email|unique:users,email,' . $this->user()->id,
            'phone'      => 'nullable|string|unique:users,phone,' . $this->user()->id,
            'gender'     => 'nullable|in:m,f',
            'password'   => 'nullable|min:8|confirmed',

            // Accept either a multipart file or a base64 data URI string
            'image'      => 'nullable',
            'image_file' => 'nullable|image|max:5120',
        ];
    }

    public function body(): array
    {
        return [

        ];
    }
}
