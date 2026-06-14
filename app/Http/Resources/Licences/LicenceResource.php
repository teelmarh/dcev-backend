<?php

namespace App\Http\Resources\Licences;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Appointments\AppointmentResource;

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
                ? Storage::disk('public')->url($this->licence_document_path)
                : null,
            'passport_photo_url' => $this->passport_photo_path
                ? Storage::disk('public')->url($this->passport_photo_path)
                : null,

            'detail' => $this->whenLoaded($detailRelation, fn () => $this->{$detailRelation}),

            'appointment' => $this->whenLoaded('appointment', fn () =>
                $this->appointment ? new AppointmentResource($this->appointment) : null
            ),

            // --- Officer-facing fields (only present when officer endpoint loads these relations) ---
            'applicant' => $this->whenLoaded('user', fn () => [
                'id'         => $this->user->id,
                'first_name' => $this->user->first_name,
                'last_name'  => $this->user->last_name,
                'email'      => $this->user->email,
                'phone'      => $this->user->phone,
                'nin'        => $this->user->nin,
            ]),

            'delivery' => $this->whenLoaded('deliveryDetail', fn () => $this->deliveryDetail ? [
                'delivery_method' => $this->deliveryDetail->delivery_method,
                'address'         => $this->deliveryDetail->address,
                'city'            => $this->deliveryDetail->city,
                'state'           => $this->deliveryDetail->state,
            ] : null),

            'enrollment_transaction' => $this->whenLoaded('enrollmentTransaction', fn () =>
                $this->enrollmentTransaction ? [
                    'id'             => $this->enrollmentTransaction->id,
                    'reference'      => $this->enrollmentTransaction->reference,
                    'amount'         => $this->enrollmentTransaction->amount,
                    'status'         => $this->enrollmentTransaction->status,
                    'payment_method' => $this->enrollmentTransaction->payment_method,
                    'paid_at'        => $this->enrollmentTransaction->paid_at,
                ] : null
            ),

            'created_at' => $this->created_at,
        ];
    }
}
