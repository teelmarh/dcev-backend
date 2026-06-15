<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentVerification extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'physical_presence_confirmed' => 'boolean',
            'nin_photo_matched'           => 'boolean',
            'age_eligible'                => 'boolean',
            'uploaded_licence_reviewed'   => 'boolean',
            'physical_licence_confirmed'  => 'boolean',
            'has_discrepancy'             => 'boolean',
            'verified_at'                 => 'datetime',
            'officer_id'                  => 'integer',
            'licence_id'                  => 'integer',
        ];
    }

    public function licence(): BelongsTo
    {
        return $this->belongsTo(Licence::class);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    /**
     * All 5 verification checks have been confirmed true.
     */
    public function allChecksPass(): bool
    {
        return $this->physical_presence_confirmed === true
            && $this->nin_photo_matched           === true
            && $this->age_eligible                === true
            && $this->uploaded_licence_reviewed   === true
            && $this->physical_licence_confirmed  === true;
    }
}
