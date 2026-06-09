<?php

namespace App\Http\Requests\Licences;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'licence_id'           => ['required', 'integer', 'exists:licences,id'],
            'recipient_name'       => ['required', 'string', 'max:255'],
            'recipient_phone'      => ['required', 'string', 'max:20'],
            'address_line'         => ['required', 'string', 'max:255'],
            'city'                 => ['required', 'string', 'max:100'],
            'state'                => ['required', 'string', 'max:100'],
            'lga'                  => ['nullable', 'string', 'max:100'],
            'postal_code'          => ['nullable', 'string', 'max:20'],
            'courier_instructions' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
