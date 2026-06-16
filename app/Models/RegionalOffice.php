<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegionalOffice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active'              => 'boolean',
        'is_pickup_location'  => 'boolean',
        'daily_capacity'      => 'integer',
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Count non-cancelled appointments for a given date.
     */
    public function bookedCountForDate(string $date): int
    {
        return $this->appointments()
            ->whereDate('scheduled_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->count();
    }

    public function hasCapacityForDate(string $date): bool
    {
        return $this->bookedCountForDate($date) < $this->daily_capacity;
    }
}
