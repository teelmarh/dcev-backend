<?php

namespace App\Http\Requests\Empic;

use Illuminate\Foundation\Http\FormRequest;

class AddAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'     => 'required|integer|exists:users,id',
            'city'        => 'required|string|max:255',
            'street_name' => 'nullable|string|max:255',
            'street_no'   => 'nullable|string|max:50',
            'building'    => 'nullable|string|max:255',
            'extra_line'  => 'nullable|string|max:255',
            'region'      => 'nullable|string|max:255',
            'zip_code'    => 'nullable|string|max:20',
        ];
    }
}
