<?php

namespace App\Http\Requests\Licences\Fcl;

use App\Http\Requests\Licences\StoreLicenceRequest;

class StorePilotLicenceRequest extends StoreLicenceRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            // comma-separated: PPL,CPL,ATPL,IR,ME
            'ratings'             => 'nullable|string|max:255',
            'aircraft_categories' => 'nullable|string|max:255',
            'endorsements'        => 'nullable|string',
        ]);
    }
}
