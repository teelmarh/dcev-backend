<?php

namespace App\Http\Requests\Admin\Officer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfficerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'regional_office_id' => ['required', 'integer', 'exists:regional_offices,id'],
        ];
    }
}
