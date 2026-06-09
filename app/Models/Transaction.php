<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount'   => 'decimal:2',
        'metadata' => 'array',
        'paid_at'  => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function transactable(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            // Enrollment: transactable is the User
            $q->where('transactable_type', (new User)->getMorphClass())
              ->where('transactable_id', $userId);
        })->orWhere(function ($q) use ($userId) {
            // Delivery: transactable is a Licence belonging to the User
            $q->where('transactable_type', (new Licence)->getMorphClass())
              ->whereIn('transactable_id', Licence::where('user_id', $userId)->select('id'));
        });
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
