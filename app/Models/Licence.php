<?php

namespace App\Models;

use App\Models\LicenceAmeDetail;
use App\Models\LicenceAsoDetail;
use App\Models\LicenceAtcDetail;
use App\Models\LicenceAtsepDetail;
use App\Models\LicenceCabinCrewDetail;
use App\Models\LicenceFlightDispatchDetail;
use App\Models\LicencePilotDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Licence extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
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
