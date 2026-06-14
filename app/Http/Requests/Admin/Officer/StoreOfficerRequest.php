<?php

namespace App\Http\Requests\Admin\Officer;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfficerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'         => ['required', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'email'              => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'              => ['required', 'string', 'max:20'],
            'regional_office_id' => ['required', 'integer', 'exists:regional_offices,id'],
        ];
    }
}
