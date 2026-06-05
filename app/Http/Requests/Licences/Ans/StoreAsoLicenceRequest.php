<?php

namespace App\Http\Requests\Licences\Ans;

use App\Http\Requests\Licences\StoreLicenceRequest;

class StoreAsoLicenceRequest extends StoreLicenceRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'aerodrome_category' => 'nullable|string|max:100',
        ]);
    }
}
