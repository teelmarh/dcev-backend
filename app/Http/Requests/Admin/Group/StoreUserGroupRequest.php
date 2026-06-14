<?php

namespace App\Http\Requests\Admin\Group;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

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

    public function after(): array
    {
        return [
            function ($validator) {
                $slug = Str::slug($this->name ?? '');
                if ($slug && \App\Models\UserGroup::where('slug', $slug)->exists()) {
                    $validator->errors()->add('name', 'A group with a similar name already exists.');
                }
            },
        ];
    }
}
