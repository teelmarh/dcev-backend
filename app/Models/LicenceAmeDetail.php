<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenceAmeDetail extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'knowledge_test_date'  => 'date',
            'skill_test_date'      => 'date',
            'ato_graduation_date'  => 'date',
            'airframe_ratings'     => 'array',
            'powerplant_ratings'   => 'array',
            'avionics_ratings'     => 'array',
        ];
    }

    public function licence(): BelongsTo
    {
        return $this->belongsTo(Licence::class);
    }
}
