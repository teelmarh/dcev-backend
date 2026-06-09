<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scheduled_date'     => ['required', 'date', 'date_format:Y-m-d', 'after:today'],
            'regional_office_id' => ['nullable', 'integer', 'exists:regional_offices,id'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ];
    }
}
