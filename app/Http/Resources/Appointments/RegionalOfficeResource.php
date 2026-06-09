<?php

namespace App\Http\Resources\Appointments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionalOfficeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'slug'                 => $this->slug,
            'state'                => $this->state,
            'city'                 => $this->city,
            'address'              => $this->address,
            'phone'                => $this->phone,
            'email'                => $this->email,
            'working_hours_start'  => $this->working_hours_start,
            'working_hours_end'    => $this->working_hours_end,
            'daily_capacity'       => $this->daily_capacity,
        ];
    }
}
