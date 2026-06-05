<?php

namespace App\Http\Requests\Licences\Ans;

use App\Http\Requests\Licences\StoreLicenceRequest;

class StoreAtcLicenceRequest extends StoreLicenceRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            // comma-separated: area_control,approach,aerodrome,ATIS,FIS
            'ratings'      => 'nullable|string|max:255',
            'unit'         => 'nullable|string|max:255',
            'endorsements' => 'nullable|string',
        ]);
    }
}
