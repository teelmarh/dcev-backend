<?php

namespace App\Http\Resources\Licences;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LicenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $detailRelation = $this->detailRelationName();

        return [
            'id'                 => $this->id,
            'family'             => $this->family,
            'type'               => $this->type,
            'application_type'   => $this->application_type,
            'licence_number'     => $this->licence_number,
            'initial_issue_date' => $this->initial_issue_date?->toDateString(),
            'last_renewal_date'  => $this->last_renewal_date?->toDateString(),
            'expiry_date'        => $this->expiry_date?->toDateString(),
            'status'             => $this->status,

            // identity
            'height'      => $this->height,
            'weight'      => $this->weight,
            'hair_colour' => $this->hair_colour,
            'eye_colour'  => $this->eye_colour,

            // licence history
            'has_prior_licence'            => $this->has_prior_licence,
            'prior_licence_suspended'      => $this->prior_licence_suspended,
            'prior_licence_suspended_date' => $this->prior_licence_suspended_date?->toDateString(),
            'prior_licence_type'           => $this->prior_licence_type,
            'prior_licence_number'         => $this->prior_licence_number,
            'prior_licence_issued_date'    => $this->prior_licence_issued_date?->toDateString(),

            // medical
            'medical_cert_held'     => $this->medical_cert_held,
            'medical_cert_class'    => $this->medical_cert_class,
            'medical_cert_date'     => $this->medical_cert_date?->toDateString(),
            'medical_examiner_name' => $this->medical_examiner_name,

            // identification
            'id_form'   => $this->id_form,
            'id_number' => $this->id_number,

            // uploaded documents
            'licence_document_url' => $this->licence_document_path
                ? rtrim(config('app.url'), '/') . '/storage/' . $this->licence_document_path
                : null,
            'passport_photo_url' => $this->passport_photo_path
                ? rtrim(config('app.url'), '/') . '/storage/' . $this->passport_photo_path
                : null,

            'detail' => $this->whenLoaded($detailRelation, fn () => $this->{$detailRelation}),

            'created_at' => $this->created_at,
        ];
    }
}
