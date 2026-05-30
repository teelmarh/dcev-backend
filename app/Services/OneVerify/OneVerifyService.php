<?php

namespace App\Services\OneVerify;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OneVerifyService
{
    private string $baseUrl;
    private string $apiKey;
    private string $userId;
    private string $email;
    private string $password;
    private int    $tokenTtl;

    private const TOKEN_CACHE_KEY = 'oneverify_token';

    public function __construct()
    {
        $this->baseUrl  = rtrim(config('oneverify.base_url'), '/');
        $this->apiKey   = config('oneverify.api_key');
        $this->userId   = config('oneverify.user_id');
        $this->email    = config('oneverify.email');
        $this->password = config('oneverify.password');
        $this->tokenTtl = config('oneverify.token_ttl');
    }

    /**
     * Look up a NIN via the OneVERIFY IDENTITY endpoint.
     * Automatically refreshes the Bearer token on a 401 and retries once.
     *
     * @return array<string, mixed>
     * @throws RuntimeException if the lookup fails after token refresh.
     */
    public function lookupNin(string $nin): array
    {
        $response = $this->client()->post('/identity/nin', ['nin' => $nin]);

        if ($response->status() === 401) {
            // Token may have expired server-side before TTL; force refresh and retry
            Cache::forget(self::TOKEN_CACHE_KEY);
            $response = $this->client()->post('/identity/nin', ['nin' => $nin]);
        }

        if ($response->failed()) {
            throw new RuntimeException(
                'OneVERIFY NIN lookup failed: ' . $response->status() . ' ' . $response->body()
            );
        }

        // Response shape: { status, message, data: { ... } }
        $data = $response->json('data');

        if (! $data) {
            throw new RuntimeException('OneVERIFY returned an empty data payload.');
        }

        return $data;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Build an Http client pre-loaded with auth headers.
     */
    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders($this->buildHeaders())
            ->withToken($this->getToken())
            ->acceptJson()
            ->asJson();
    }

    /**
     * Return a cached Bearer token, fetching a fresh one when necessary.
     *
     * @throws RuntimeException if login fails.
     */
    private function getToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, $this->tokenTtl, function () {
            $response = Http::baseUrl($this->baseUrl)
                ->withHeaders($this->buildHeaders(withAuth: false))
                ->acceptJson()
                ->asJson()
                ->post('/login', [
                    'email'    => $this->email,
                    'password' => $this->password,
                ]);

            if ($response->failed()) {
                throw new RuntimeException(
                    'OneVERIFY authentication failed: ' . $response->status() . ' ' . $response->body()
                );
            }

            $token = $response->json('token') ?? $response->json('data.token') ?? $response->json('access_token');

            if (! $token) {
                throw new RuntimeException('OneVERIFY login response did not contain a token.');
            }

            return $token;
        });
    }

    /**
     * Assemble shared request headers.
     * Pass withAuth=false when calling the login endpoint itself.
     */
    private function buildHeaders(bool $withAuth = true): array
    {
        $headers = [
            'X-API-KEY'  => $this->apiKey,
            'X-USER-ID'  => $this->userId,
        ];

        return $headers;
    }
}
