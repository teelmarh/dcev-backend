<?php

namespace App\Services\Payment;

use App\Models\Licence;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Initiate an enrollment transaction for the given user.
     */
    public function initiateEnrollment(User $user, string $gateway): Transaction
    {
        // Idempotent: return existing pending enrollment if one exists
        $existing = Transaction::where('transactable_type', $user->getMorphClass())
            ->where('transactable_id', $user->id)
            ->ofType('enrollment')
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($existing) {
            return $existing;
        }

        $transaction = Transaction::create([
            'transactable_type' => $user->getMorphClass(),
            'transactable_id'   => $user->id,
            'type'              => 'enrollment',
            'amount'            => config('services.dcev.enrollment_fee', 15000),
            'currency'          => 'NGN',
            'reference'         => $this->generateReference('ENR'),
            'status'            => 'pending',
            'gateway'           => $gateway,
        ]);

        $gatewayInstance = PaymentGatewayFactory::make($gateway);
        $result          = $gatewayInstance->initiate($transaction);

        $transaction->update([
            'gateway_reference' => $result['gateway_reference'],
            'metadata'          => ['payment_url' => $result['payment_url']],
        ]);

        return $transaction->fresh();
    }

    /**
     * Initiate a delivery transaction for a specific licence.
     */
    public function initiateDelivery(Licence $licence, string $gateway): Transaction
    {
        $existing = Transaction::where('transactable_type', $licence->getMorphClass())
            ->where('transactable_id', $licence->id)
            ->ofType('delivery')
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($existing) {
            return $existing;
        }

        $transaction = Transaction::create([
            'transactable_type' => $licence->getMorphClass(),
            'transactable_id'   => $licence->id,
            'type'              => 'delivery',
            'amount'            => config('services.dcev.delivery_fee', 5000),
            'currency'          => 'NGN',
            'reference'         => $this->generateReference('DLV'),
            'status'            => 'pending',
            'gateway'           => $gateway,
        ]);

        $gatewayInstance = PaymentGatewayFactory::make($gateway);
        $result          = $gatewayInstance->initiate($transaction);

        $transaction->update([
            'gateway_reference' => $result['gateway_reference'],
            'metadata'          => ['payment_url' => $result['payment_url']],
        ]);

        return $transaction->fresh();
    }

    /**
     * Verify a transaction by internal reference. Marks paid if gateway confirms.
     */
    public function verify(string $reference): Transaction
    {
        $transaction = Transaction::where('reference', $reference)->firstOrFail();

        if ($transaction->isPaid()) {
            return $transaction;
        }

        $gateway   = PaymentGatewayFactory::make($transaction->gateway);
        $confirmed = $gateway->verify($transaction->gateway_reference);

        if ($confirmed) {
            $transaction->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);
        }

        return $transaction->fresh();
    }

    /**
     * Check whether the user has a paid enrollment transaction.
     */
    public function userHasPaidEnrollment(User $user): bool
    {
        return Transaction::where('transactable_type', $user->getMorphClass())
            ->where('transactable_id', $user->id)
            ->ofType('enrollment')
            ->paid()
            ->exists();
    }

    private function generateReference(string $prefix): string
    {
        do {
            $ref = 'DCEV-' . $prefix . '-' . strtoupper(Str::random(10));
        } while (Transaction::where('reference', $ref)->exists());

        return $ref;
    }
}
