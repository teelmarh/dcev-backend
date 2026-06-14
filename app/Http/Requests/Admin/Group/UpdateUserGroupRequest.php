<?php

namespace App\Http\Requests\Admin\Group;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:100', Rule::unique('user_groups', 'name')->ignore($this->route('group'))],
            'description' => ['nullable', 'string', 'max:255'],
            'active'      => ['sometimes', 'boolean'],
        ];
    }
}
