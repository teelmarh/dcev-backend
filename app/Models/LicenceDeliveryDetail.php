<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenceDeliveryDetail extends Model
{
    protected $guarded = [];

    public function licence(): BelongsTo
    {
        return $this->belongsTo(Licence::class);
    }

    public function deliveryTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'delivery_transaction_id');
    }

    public function isPaid(): bool
    {
        return $this->delivery_transaction_id !== null
            && $this->deliveryTransaction?->isPaid();
    }
}
