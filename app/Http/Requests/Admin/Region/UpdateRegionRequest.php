<?php

namespace App\Http\Requests\Admin\Region;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRegionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'region_id'           => ['required', 'integer', 'exists:regional_offices,id'],
            'name'                => ['sometimes', 'string', 'max:120', "unique:regional_offices,name,{$this->region_id}"],
            'state'               => ['sometimes', 'string', 'max:60'],
            'city'                => ['sometimes', 'string', 'max:60'],
            'address'             => ['sometimes', 'string', 'max:255'],
            'phone'               => ['sometimes', 'nullable', 'string', 'max:20'],
            'email'               => ['sometimes', 'nullable', 'email', 'max:120'],
            'daily_capacity'      => ['sometimes', 'integer', 'min:1', 'max:9999'],
            'working_hours_start' => ['sometimes', 'date_format:H:i'],
            'working_hours_end'   => ['sometimes', 'date_format:H:i', 'after:working_hours_start'],
            'active'              => ['sometimes', 'boolean'],
        ];
    }
}
