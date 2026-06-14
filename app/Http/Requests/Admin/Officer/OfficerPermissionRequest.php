<?php

namespace App\Http\Requests\Admin\Officer;

use Illuminate\Foundation\Http\FormRequest;

class OfficerPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'permission_id' => ['required', 'integer', 'exists:permissions,id'],
        ];
    }
}
