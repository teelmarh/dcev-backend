<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiometricCapture extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'licence_id'   => 'integer',
            'captured_by'  => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function licence(): BelongsTo
    {
        return $this->belongsTo(Licence::class);
    }

    public function capturedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'captured_by');
    }

    public function isComplete(): bool
    {
        return $this->photo_path !== null
            && $this->left_thumb_wsq !== null
            && $this->left_index_wsq !== null
            && $this->right_thumb_wsq !== null
            && $this->right_index_wsq !== null
            && $this->signature_path !== null;
    }
}
