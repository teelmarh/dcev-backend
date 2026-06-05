<?php

namespace App\Http\Requests\Licences;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared validation rules for all licence store requests.
 * Each type-specific request extends this class and calls
 * array_merge(parent::rules(), [...type rules...]).
 */
abstract class StoreLicenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // --- Base licence fields ---
            'licence_number'    => 'nullable|string|max:100',
            'initial_issue_date' => 'required|date',
            'last_renewal_date'  => 'nullable|date|after_or_equal:initial_issue_date',
            'expiry_date'        => 'nullable|date',
            'status'             => 'nullable|string|in:active,suspended,revoked,expired',

            // Physical identity
            'height'      => 'nullable|string|max:20',
            'weight'      => 'nullable|string|max:20',
            'hair_colour' => 'nullable|string|max:50',
            'eye_colour'  => 'nullable|string|max:50',

            // Licence history
            'has_prior_licence'            => 'nullable|boolean',
            'prior_licence_suspended'      => 'nullable|boolean',
            'prior_licence_suspended_date' => 'nullable|date',
            'prior_licence_type'           => 'nullable|string|max:100',
            'prior_licence_number'         => 'nullable|string|max:100',
            'prior_licence_issued_date'    => 'nullable|date',

            // Medical
            'medical_cert_held'       => 'nullable|boolean',
            'medical_cert_class'      => 'nullable|string|max:50',
            'medical_cert_date'       => 'nullable|date',
            'medical_examiner_name'   => 'nullable|string|max:255',

            // Identification
            'id_form'   => 'nullable|string|max:100',
            'id_number' => 'nullable|string|max:100',

            // --- Training / basis (Section II) ---
            'knowledge_test_date'    => 'nullable|date',
            'skill_test_date'        => 'nullable|date',
            'skill_test_aircraft'    => 'nullable|string|max:100',
            'skill_test_total_time'  => 'nullable|string|max:50',

            // ATO
            'ato_name'            => 'nullable|string|max:255',
            'ato_location'        => 'nullable|string|max:255',
            'ato_number'          => 'nullable|string|max:100',
            'ato_course'          => 'nullable|string|max:255',
            'ato_graduation_date' => 'nullable|date',

            // Foreign licence
            'foreign_country'        => 'nullable|string|max:100',
            'foreign_licence_grade'  => 'nullable|string|max:100',
            'foreign_licence_number' => 'nullable|string|max:100',
            'foreign_ratings'        => 'nullable|string|max:255',

            // Uploaded documents
            'licence_document' => 'nullable|file|mimes:pdf|max:10240',     // max 10 MB
            'passport_photo'   => 'nullable|file|mimes:jpeg,jpg,png|max:5120', // max 5 MB
        ];
    }

    /**
     * Pluck only the base licence fields from validated data.
     * If the user is NIN-verified, id_form is always stored as 'NIN'
     * and id_number defaults to the user's NIN unless explicitly provided.
     */
    public function baseLicenceData(): array
    {
        $data = $this->safe()->only([
            'licence_number', 'initial_issue_date', 'last_renewal_date', 'expiry_date', 'status',
            'height', 'weight', 'hair_colour', 'eye_colour',
            'has_prior_licence', 'prior_licence_suspended', 'prior_licence_suspended_date',
            'prior_licence_type', 'prior_licence_number', 'prior_licence_issued_date',
            'medical_cert_held', 'medical_cert_class', 'medical_cert_date', 'medical_examiner_name',
            'id_form', 'id_number',
        ]);

        $user = $this->user();

        if ($user->nin_verified) {
            $data['id_form']   = 'NIN';
            $data['id_number'] = $data['id_number'] ?? $user->nin;
        }

        if ($this->hasFile('licence_document')) {
            $data['licence_document_path'] = $this->file('licence_document')
                ->store("licences/{$user->id}/documents", 'local');
        }

        if ($this->hasFile('passport_photo')) {
            $data['passport_photo_path'] = $this->file('passport_photo')
                ->store("licences/{$user->id}/photos", 'local');
        }

        return $data;
    }

    /**
     * Pluck only the shared training/basis fields for the detail table.
     */
    public function sharedDetailData(): array
    {
        return $this->safe()->only([
            'knowledge_test_date', 'skill_test_date', 'skill_test_aircraft', 'skill_test_total_time',
            'ato_name', 'ato_location', 'ato_number', 'ato_course', 'ato_graduation_date',
            'foreign_country', 'foreign_licence_grade', 'foreign_licence_number', 'foreign_ratings',
        ]);
    }
}
