<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'licence_id'         => ['required', 'integer', 'exists:licences,id'],
            'regional_office_id' => ['required', 'integer', 'exists:regional_offices,id'],
            'scheduled_date'     => ['required', 'date', 'date_format:Y-m-d', 'after:today'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ];
    }
}
