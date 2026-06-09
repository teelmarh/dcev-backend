<?php

namespace App\Http\Resources\Transactions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'type'               => $this->type,
            'amount'             => $this->amount,
            'currency'           => $this->currency,
            'reference'          => $this->reference,
            'gateway'            => $this->gateway,
            'status'             => $this->status,
            'paid_at'            => $this->paid_at?->toISOString(),
            'payment_url'        => $this->metadata['payment_url'] ?? null,
        ];
    }
}
