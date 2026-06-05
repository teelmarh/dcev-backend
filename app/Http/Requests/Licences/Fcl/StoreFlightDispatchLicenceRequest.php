<?php

namespace App\Http\Requests\Licences\Fcl;

use App\Http\Requests\Licences\StoreLicenceRequest;

class StoreFlightDispatchLicenceRequest extends StoreLicenceRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'ratings' => 'nullable|string|max:255',
        ]);
    }
}
