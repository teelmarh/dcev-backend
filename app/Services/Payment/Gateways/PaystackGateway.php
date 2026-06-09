<?php

namespace App\Services\Payment\Gateways;

use App\Models\Transaction;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackGateway implements PaymentGatewayInterface
{
    private string $secretKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
        $this->baseUrl   = 'https://api.paystack.co';
    }

    public function initiate(Transaction $transaction): array
    {
        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transaction/initialize", [
                'email'     => $this->resolveEmail($transaction),
                'amount'    => (int) ($transaction->amount * 100), // kobo
                'reference' => $transaction->reference,
                'currency'  => $transaction->currency,
                'metadata'  => [
                    'transaction_id' => $transaction->id,
                    'type'           => $transaction->type,
                ],
            ]);

        $body = $response->json();

        if (! $response->successful() || ! ($body['status'] ?? false)) {
            throw new \RuntimeException('Paystack initiation failed: ' . ($body['message'] ?? 'unknown error'));
        }

        return [
            'payment_url'       => $body['data']['authorization_url'],
            'gateway_reference' => $body['data']['reference'],
        ];
    }

    public function verify(string $gatewayReference): bool
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transaction/verify/{$gatewayReference}");

        $body = $response->json();

        return $response->successful()
            && ($body['status'] ?? false)
            && ($body['data']['status'] ?? '') === 'success';
    }

    public function handleWebhook(Request $request): void
    {
        // Validate Paystack signature
        $hash = hash_hmac('sha512', $request->getContent(), $this->secretKey);

        if ($hash !== $request->header('X-Paystack-Signature')) {
            abort(401, 'Invalid webhook signature');
        }

        $payload = $request->json()->all();
        $event   = $payload['event'] ?? '';

        if ($event !== 'charge.success') {
            return;
        }

        $reference = $payload['data']['reference'] ?? null;

        if (! $reference) {
            return;
        }

        $transaction = Transaction::where('reference', $reference)
            ->orWhere('gateway_reference', $reference)
            ->first();

        if (! $transaction || $transaction->isPaid()) {
            return;
        }

        $transaction->update([
            'status'   => 'paid',
            'paid_at'  => now(),
            'metadata' => $payload['data'],
        ]);

        Log::info('Paystack webhook: transaction paid', ['reference' => $reference]);
    }

    private function resolveEmail(Transaction $transaction): string
    {
        $transactable = $transaction->transactable;

        return $transactable instanceof \App\Models\User
            ? $transactable->email
            : $transactable->user->email;
    }
}
