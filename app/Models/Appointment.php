<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'scheduled_date' => 'date',
        'previous_date'  => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function licence(): BelongsTo
    {
        return $this->belongsTo(Licence::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(RegionalOffice::class, 'regional_office_id');
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function isReschedulable(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }
}
