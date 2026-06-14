<?php

namespace App\Http\Resources\Officer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficerLicenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'type'                   => $this->type,
            'status'                 => $this->status,
            'pickup_office_id'       => $this->pickup_office_id,
            'initial_issue_date'     => $this->initial_issue_date,
            'expiry_date'            => $this->expiry_date,
            'created_at'             => $this->created_at,
            'applicant'              => $this->whenLoaded('user', fn () => [
                'id'         => $this->user->id,
                'first_name' => $this->user->first_name,
                'last_name'  => $this->user->last_name,
                'email'      => $this->user->email,
                'phone'      => $this->user->phone,
                'nin'        => $this->user->nin,
            ]),
            'appointment'            => $this->whenLoaded('appointment', fn () =>
                $this->appointment ? new OfficerAppointmentResource($this->appointment) : null
            ),
            'delivery'               => $this->whenLoaded('deliveryDetail', fn () => $this->deliveryDetail ? [
                'delivery_method' => $this->deliveryDetail->delivery_method,
                'address'         => $this->deliveryDetail->address,
                'city'            => $this->deliveryDetail->city,
                'state'           => $this->deliveryDetail->state,
            ] : null),
        ];
    }
}
