<?php

namespace App\Http\Resources\Officer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentVerificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'licence_id' => $this->licence_id,
            'officer'    => $this->whenLoaded('officer', fn () => $this->officer ? [
                'id'         => $this->officer->id,
                'first_name' => $this->officer->first_name,
                'last_name'  => $this->officer->last_name,
                'email'      => $this->officer->email,
            ] : null),

            // Checklist
            'checks' => [
                'physical_presence_confirmed' => $this->physical_presence_confirmed,
                'nin_photo_matched'           => $this->nin_photo_matched,
                'age_eligible'                => $this->age_eligible,
                'uploaded_licence_reviewed'   => $this->uploaded_licence_reviewed,
                'physical_licence_confirmed'  => $this->physical_licence_confirmed,
            ],
            'all_checks_pass' => $this->allChecksPass(),

            // Discrepancy
            'has_discrepancy'     => $this->has_discrepancy,
            'discrepancy_type'    => $this->discrepancy_type,
            'discrepancy_remarks' => $this->discrepancy_remarks,

            // Escalation
            'escalation_reason' => $this->escalation_reason,

            // Timestamps
            'verified_at' => $this->verified_at?->toDateTimeString(),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
