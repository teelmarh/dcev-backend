<?php

namespace App\Http\Resources\Appointments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'licence_id'     => $this->licence_id,
            'scheduled_date' => $this->scheduled_date->toDateString(),
            'scheduled_time' => $this->scheduled_time,
            'previous_date'  => $this->previous_date?->toDateString(),
            'previous_time'  => $this->previous_time,
            'status'         => $this->status,
            'notes'          => $this->notes,
            'attended_at'    => $this->attended_at?->toISOString(),
            'office'         => new RegionalOfficeResource($this->whenLoaded('office')),
            'created_at'     => $this->created_at->toISOString(),
        ];
    }
}
