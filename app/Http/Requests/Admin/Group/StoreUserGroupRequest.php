<?php

namespace App\Http\Requests\Admin\Group;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100', 'unique:user_groups,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'active'      => ['sometimes', 'boolean'],
        ];
    }
}
