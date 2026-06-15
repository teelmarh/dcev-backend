<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $guarded = [];

    /**
     * Audit logs are append-only — never update an existing row.
     */
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'payload'    => 'array',
            'subject_id' => 'integer',
            'user_id'    => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retrieve audit entries for a given subject model.
     */
    public function scopeForSubject($query, Model $subject): mixed
    {
        return $query
            ->where('subject_type', get_class($subject))
            ->where('subject_id', $subject->getKey());
    }
}
