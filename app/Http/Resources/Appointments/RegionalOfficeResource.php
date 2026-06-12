<?php

namespace App\Http\Resources\Appointments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionalOfficeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'slug'    => $this->slug,
            'state'   => $this->state,
            'city'    => $this->city,
            'address' => $this->address,
            'phone'   => $this->phone,
            'email'   => $this->email,
            // Presented as 100 to the client; actual internal cap is 96
            'daily_capacity' => 100,
        ];
    }
}
