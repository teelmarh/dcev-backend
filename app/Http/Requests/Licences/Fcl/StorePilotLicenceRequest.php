<?php

namespace App\Http\Requests\Licences\Fcl;

use App\Http\Requests\Licences\StoreLicenceRequest;

class StorePilotLicenceRequest extends StoreLicenceRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'ratings'              => 'nullable|string|max:255',
            'aircraft_categories'  => 'nullable|string|max:255',
            'endorsements'         => 'nullable|string',
            // Skill test PIC time (O-PEL 001 III.B.3)
            'skill_test_pic_time'  => 'nullable|string|max:50',
            // Military qualifications basis (O-PEL 001 III.E)
            'military_service'     => 'nullable|string|in:Navy,Airforce,Army',
            'military_date_rated'  => 'nullable|date',
            'military_rank_grade'  => 'nullable|string|max:255',
        ]);
    }
}
