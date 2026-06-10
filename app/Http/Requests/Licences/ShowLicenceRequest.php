<?php

namespace App\Http\Requests\Licences;

use Illuminate\Foundation\Http\FormRequest;

class ShowLicenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'licence_id' => ['required', 'integer', 'exists:licences,id'],
        ];
    }
}
