<?php

namespace App\Services\Payment\Gateways;

use App\Models\Transaction;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RemitaGateway implements PaymentGatewayInterface
{
    private string $merchantId;
    private string $serviceTypeId;
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->merchantId    = config('services.remita.merchant_id');
        $this->serviceTypeId = config('services.remita.service_type_id');
        $this->apiKey        = config('services.remita.api_key');
        $this->baseUrl       = config('services.remita.sandbox', true)
            ? 'https://remitademo.net/remita/exapp/api/v1/send/api'
            : 'https://login.remita.net/remita/exapp/api/v1/send/api';
    }

    public function initiate(Transaction $transaction): array
    {
        $email  = $this->resolveEmail($transaction);
        $hash   = hash('sha512', $this->merchantId . $this->serviceTypeId . $transaction->reference . $transaction->amount . $this->apiKey);

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => "remitaConsumerKey={$this->merchantId},remitaConsumerToken={$hash}",
        ])->post("{$this->baseUrl}/echannelsvc/merchant/api/paymentinit", [
            'serviceTypeId' => $this->serviceTypeId,
            'amount'        => (string) $transaction->amount,
            'orderId'       => $transaction->reference,
            'payerName'     => $email,
            'payerEmail'    => $email,
            'payerPhone'    => '',
            'description'   => ucfirst($transaction->type) . ' fee — DCEV',
        ]);

        // Remita returns JSONP — strip callback wrapper if present
        $raw  = $response->body();
        $json = preg_replace('/^[^(]+\(|\);?$/', '', $raw);
        $body = json_decode($json, true);

        if (empty($body['RRR'])) {
            throw new \RuntimeException('Remita initiation failed: ' . ($body['statusMessage'] ?? 'unknown error'));
        }

        $rrr        = $body['RRR'];
        $paymentUrl = config('services.remita.sandbox', true)
            ? "https://remitademo.net/remita/ecomm/finalize.reg?merchantId={$this->merchantId}&hash={$hash}&RRR={$rrr}"
            : "https://login.remita.net/remita/ecomm/finalize.reg?merchantId={$this->merchantId}&hash={$hash}&RRR={$rrr}";

        return [
            'payment_url'       => $paymentUrl,
            'gateway_reference' => $rrr,
        ];
    }

    public function verify(string $gatewayReference): bool
    {
        $hash = hash('sha512', $gatewayReference . $this->apiKey . $this->merchantId);

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => "remitaConsumerKey={$this->merchantId},remitaConsumerToken={$hash}",
        ])->get("{$this->baseUrl}/echannelsvc/{$this->merchantId}/{$gatewayReference}/{$hash}/status.reg");

        $body = $response->json();

        // Remita status 00 = successful
        return $response->successful() && ($body['status'] ?? '') === '00';
    }

    public function handleWebhook(Request $request): void
    {
        // Remita does not have a standard webhook — relies on polling/verify
        // This endpoint accepts their notification callback if configured
        $payload = $request->json()->all();
        $rrr     = $payload['RRR'] ?? null;

        if (! $rrr) {
            return;
        }

        $transaction = Transaction::where('gateway_reference', $rrr)->first();

        if (! $transaction || $transaction->isPaid()) {
            return;
        }

        // Re-verify before marking paid
        if ($this->verify($rrr)) {
            $transaction->update([
                'status'   => 'paid',
                'paid_at'  => now(),
                'metadata' => $payload,
            ]);

            Log::info('Remita webhook: transaction paid', ['rrr' => $rrr]);
        }
    }

    private function resolveEmail(Transaction $transaction): string
    {
        $transactable = $transaction->transactable;

        return $transactable instanceof \App\Models\User
            ? $transactable->email
            : $transactable->user->email;
    }
}
