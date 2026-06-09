<?php

namespace App\Services\Payment\Contracts;

use App\Models\Transaction;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment. Returns ['payment_url' => ..., 'gateway_reference' => ...].
     */
    public function initiate(Transaction $transaction): array;

    /**
     * Verify a transaction by gateway reference. Returns true if paid.
     */
    public function verify(string $gatewayReference): bool;

    /**
     * Handle a server-to-server webhook from the gateway.
     * Should validate signature and update transaction status.
     */
    public function handleWebhook(Request $request): void;
}
