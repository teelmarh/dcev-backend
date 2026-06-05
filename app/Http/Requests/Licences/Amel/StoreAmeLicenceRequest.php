<?php

namespace App\Http\Requests\Licences\Amel;

use App\Http\Requests\Licences\StoreLicenceRequest;

class StoreAmeLicenceRequest extends StoreLicenceRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            // comma-separated sub-ratings per discipline group
            'airframe_ratings'   => 'nullable|string|max:255',
            'powerplant_ratings' => 'nullable|string|max:255',
            'avionics_ratings'   => 'nullable|string|max:255',
            'aircraft_types'     => 'nullable|string|max:255',
            'scope_of_work'      => 'nullable|string',
            // employment
            'employer_name'      => 'nullable|string|max:255',
            'employer_city_state' => 'nullable|string|max:255',
            'employed_as'        => 'nullable|string|in:Engineer,Technician,OJT,Others',
        ]);
    }
}
