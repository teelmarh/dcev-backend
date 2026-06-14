<?php

namespace App\Http\Requests\Admin\Region;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:120', 'unique:regional_offices,name'],
            'state'               => ['required', 'string', 'max:60'],
            'city'                => ['required', 'string', 'max:60'],
            'address'             => ['required', 'string', 'max:255'],
            'phone'               => ['nullable', 'string', 'max:20'],
            'email'               => ['nullable', 'email', 'max:120'],
            'daily_capacity'      => ['nullable', 'integer', 'min:1', 'max:9999'],
            'working_hours_start' => ['nullable', 'date_format:H:i'],
            'working_hours_end'   => ['nullable', 'date_format:H:i', 'after:working_hours_start'],
            'active'              => ['nullable', 'boolean'],
        ];
    }
}
