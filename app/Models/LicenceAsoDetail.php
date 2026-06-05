<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenceAsoDetail extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'knowledge_test_date' => 'date',
            'skill_test_date'     => 'date',
            'ato_graduation_date' => 'date',
        ];
    }

    public function licence(): BelongsTo
    {
        return $this->belongsTo(Licence::class);
    }
}
