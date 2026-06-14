<?php

namespace App\Http\Resources\Officer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficerAppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'licence_id'     => $this->licence_id,
            'scheduled_date' => $this->scheduled_date,
            'scheduled_time' => $this->scheduled_time,
            'status'         => $this->status,
            'notes'          => $this->notes,
            'office'         => $this->whenLoaded('office', fn () => $this->office ? [
                'id'      => $this->office->id,
                'name'    => $this->office->name,
                'address' => $this->office->address,
            ] : null),
            'applicant'      => $this->whenLoaded('licence', fn () =>
                $this->licence?->user ? [
                    'id'         => $this->licence->user->id,
                    'first_name' => $this->licence->user->first_name,
                    'last_name'  => $this->licence->user->last_name,
                    'email'      => $this->licence->user->email,
                    'phone'      => $this->licence->user->phone,
                    'nin'        => $this->licence->user->nin,
                ] : null
            ),
        ];
    }
}
