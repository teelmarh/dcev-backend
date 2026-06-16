<?php

namespace App\Models;

use App\Models\LicenceAmeDetail;
use App\Models\LicenceAsoDetail;
use App\Models\LicenceAtcDetail;
use App\Models\LicenceAtsepDetail;
use App\Models\LicenceCabinCrewDetail;
use App\Models\LicenceFlightDispatchDetail;
use App\Models\LicencePilotDetail;
use App\Models\RegionalOffice;
use App\Models\EnrollmentVerification;
use App\Models\BiometricCapture;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Licence extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'user_id'                      => 'integer',
            'pickup_office_id'             => 'integer',
            'processed_by'                 => 'integer',
            'processed_at'                 => 'datetime',
            'initial_issue_date'           => 'date',
            'last_renewal_date'            => 'date',
            'expiry_date'                  => 'date',
            'has_prior_licence'            => 'boolean',
            'prior_licence_suspended'      => 'boolean',
            'prior_licence_suspended_date' => 'date',
            'prior_licence_issued_date'    => 'date',
            'medical_cert_held'            => 'boolean',
            'medical_cert_date'            => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function pickupOffice(): BelongsTo
    {
        return $this->belongsTo(RegionalOffice::class, 'pickup_office_id');
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class)->whereNotIn('status', ['cancelled'])->latestOfMany();
    }

    public function pilotDetail(): HasOne
    {
        return $this->hasOne(LicencePilotDetail::class);
    }

    public function cabinCrewDetail(): HasOne
    {
        return $this->hasOne(LicenceCabinCrewDetail::class);
    }

    public function flightDispatchDetail(): HasOne
    {
        return $this->hasOne(LicenceFlightDispatchDetail::class);
    }

    public function atcDetail(): HasOne
    {
        return $this->hasOne(LicenceAtcDetail::class);
    }

    public function atsepDetail(): HasOne
    {
        return $this->hasOne(LicenceAtsepDetail::class);
    }

    public function asoDetail(): HasOne
    {
        return $this->hasOne(LicenceAsoDetail::class);
    }

    public function ameDetail(): HasOne
    {
        return $this->hasOne(LicenceAmeDetail::class);
    }

    public function deliveryDetail(): HasOne
    {
        return $this->hasOne(LicenceDeliveryDetail::class);
    }

    public function enrollmentVerification(): HasOne
    {
        return $this->hasOne(EnrollmentVerification::class);
    }

    public function biometricCapture(): HasOne
    {
        return $this->hasOne(BiometricCapture::class);
    }

    public function enrollmentTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'enrollment_transaction_id');
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactable');
    }

    /**
     * Returns the correct detail relation name for this licence's type.
     */
    public function detailRelationName(): string
    {
        return match ($this->type) {
            'pilot'           => 'pilotDetail',
            'cabin_crew'      => 'cabinCrewDetail',
            'flight_dispatch' => 'flightDispatchDetail',
            'atc'             => 'atcDetail',
            'atsep'           => 'atsepDetail',
            'aso'             => 'asoDetail',
            'ame'             => 'ameDetail',
        };
    }
}
