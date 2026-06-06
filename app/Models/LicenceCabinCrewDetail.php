<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenceCabinCrewDetail extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'knowledge_test_date'  => 'date',
            'skill_test_date'      => 'date',
            'ato_graduation_date'  => 'date',
            'valid_from'           => 'date',
            'valid_to'             => 'date',
            'last_evacuation_date' => 'date',
            'last_ditching_date'   => 'date',
            'last_fire_drill_date' => 'date',
        ];
    }

    public function licence(): BelongsTo
    {
        return $this->belongsTo(Licence::class);
    }
}
