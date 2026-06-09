<?php

namespace App\Http\Requests\Transactions;

use Illuminate\Foundation\Http\FormRequest;

class InitiateDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'licence_id' => ['required', 'integer', 'exists:licences,id'],
            'gateway'    => ['required', 'string', 'in:remita,paystack'],
        ];
    }
}
