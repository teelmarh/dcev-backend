<?php

namespace App\Http\Requests\Licences\Ans;

use App\Http\Requests\Licences\StoreLicenceRequest;

class StoreAtsepLicenceRequest extends StoreLicenceRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            // comma-separated: communication,navigation,surveillance,airfield_vls
            'ratings' => 'nullable|string|max:255',
        ]);
    }
}
