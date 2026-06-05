<?php

namespace App\Http\Requests\Licences\Amel;

use App\Http\Requests\Licences\StoreLicenceRequest;

class StoreAmeLicenceRequest extends StoreLicenceRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            // comma-separated: A,B1,B2,C
            'categories'    => 'nullable|string|max:100',
            'aircraft_types' => 'nullable|string|max:255',
            'scope_of_work' => 'nullable|string',
        ]);
    }
}
