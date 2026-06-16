<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'user_id'        => 'integer',
        'scheduled_date' => 'date',
        'previous_date'  => 'date',
        'scheduled_time' => 'string',
        'previous_time'  => 'string',
        'attended_at'    => 'datetime',
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
