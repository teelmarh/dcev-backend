<?php

namespace App\Http\Requests\Licences\Fcl;

use App\Http\Requests\Licences\StoreLicenceRequest;

class StoreCabinCrewLicenceRequest extends StoreLicenceRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'operator'      => 'nullable|string|max:255',
            'aircraft_types' => 'nullable|string|max:255',
            'valid_from'    => 'nullable|date',
            'valid_to'      => 'nullable|date|after_or_equal:valid_from',
        ]);
    }
}
