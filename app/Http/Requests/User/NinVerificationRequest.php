<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class NinVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nin' => ['required', 'string', 'size:11', 'regex:/^[0-9]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'nin.size'  => 'NIN must be exactly 11 digits.',
            'nin.regex' => 'NIN must contain digits only.',
        ];
    }
}
