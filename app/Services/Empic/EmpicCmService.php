<?php

namespace App\Services\Empic;

use App\Exceptions\Empic\EmpicApiException;
use App\Exceptions\Empic\EmpicUnavailableException;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\Log;

class EmpicCmService
{
    private string $baseUrl;
    private string $clientId;
    private string $basicCredential;
    private int    $timeout;

    private const HUMAN_GRAPHQL_PATH   = '/api/cm/v1/humans/graphql';
    private const ADDRESS_GRAPHQL_PATH = '/api/cm/v1/humans/graphql';

    public function __construct()
    {
        $this->baseUrl         = rtrim(config('empic.base_url'), '/');
        $this->clientId        = config('empic.client_id');
        $this->basicCredential = base64_encode(config('empic.username') . ':' . config('empic.password'));
        $this->timeout         = config('empic.timeout');
    }

    /**
     * Register a new human in EMPIC CM.
     * Maps the User model's NIN-verified fields to HumanInput.
     *
     * @throws EmpicUnavailableException
     * @throws EmpicApiException
     */
    public function createHuman(User $user): int
    {
        $mutation = <<<'GQL'
            mutation CreateHuman($human: HumanInput!) {
                createHuman(human: $human) {
                    customerNo
                }
            }
        GQL;

        $variables = ['human' => $this->buildHumanInput($user)];

        $response = $this->send(self::HUMAN_GRAPHQL_PATH, $mutation, $variables);

        $customerNo = $response['data']['createHuman']['customerNo'] ?? null;

        if (! $customerNo) {
            throw new EmpicApiException('createHuman succeeded but returned no customerNo.', $response);
        }

        return (int) $customerNo;
    }

    /**
     * Add an address to an existing EMPIC CM human record.
     *
     * @param  array<string, mixed>  $addressData  Validated fields from AddAddressRequest
     * @throws EmpicUnavailableException
     * @throws EmpicApiException
     */
    public function addAddress(int $customerNo, array $addressData): int
    {
        $mutation = <<<'GQL'
            mutation AddAddress($address: AddressInput!) {
                addAddress(address: $address) {
                    addressId
                }
            }
        GQL;

        $variables = ['address' => $this->buildAddressInput($customerNo, $addressData)];

        $response = $this->send(self::ADDRESS_GRAPHQL_PATH, $mutation, $variables);

        $addressId = $response['data']['addAddress']['addressId'] ?? null;

        if (! $addressId) {
            throw new EmpicApiException('addAddress succeeded but returned no addressId.', $response);
        }

        return (int) $addressId;
    }

    // -------------------------------------------------------------------------
    // Private — HTTP
    // -------------------------------------------------------------------------

    /**
     * Execute a GraphQL mutation and return the decoded JSON body.
     *
     * @throws EmpicUnavailableException  On timeout or 5xx
     * @throws EmpicApiException          On 4xx or unexpected response shape
     */
    private function send(string $path, string $mutation, array $variables): array
    {
        try {
            $response = $this->client()->post($path, [
                'query'     => $mutation,
                'variables' => $variables,
            ]);
        } catch (ConnectionException $e) {
            throw new EmpicUnavailableException('EMPIC connection timed out: ' . $e->getMessage());
        }

        if ($response->serverError()) {
            throw new EmpicUnavailableException(
                'EMPIC returned a server error: ' . $response->status() . ' ' . $response->body()
            );
        }

        if ($response->clientError()) {
            throw new EmpicApiException(
                'EMPIC rejected the request: ' . $response->status() . ' ' . $response->body(),
                $response->json() ?? []
            );
        }

        $body = $response->json();

        // GraphQL errors surface as HTTP 200 with an "errors" key
        if (! empty($body['errors'])) {
            $message = collect($body['errors'])->pluck('message')->implode('; ');
            throw new EmpicApiException('EMPIC GraphQL error: ' . $message, $body);
        }

        return $body;
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withHeaders([
                'Accept'               => 'application/json',
                'de.empic.api-client-id' => $this->clientId,
                'Authorization'        => 'Basic ' . $this->basicCredential,
            ])
            ->asJson();
    }

    // -------------------------------------------------------------------------
    // Private — Payload builders
    // -------------------------------------------------------------------------

    private function buildHumanInput(User $user): array
    {
        $input = [
            'firstName' => $user->first_name,
            'status'    => [
                'catalogueType' => 2,
                'codes'         => [['name' => 'Code', 'value' => '1']],
            ],
        ];

        if ($user->last_name) {
            $input['lastName'] = $user->last_name;
        }

        if ($user->date_of_birth) {
            $input['dateOfBirth'] = $user->date_of_birth->format('Y-m-d');
        }

        if ($user->email) {
            $input['email'] = $user->email;
        }

        if ($user->phone) {
            $input['mobile'] = $user->phone;
        }

        if ($user->gender) {
            $input['sex'] = [
                'catalogueType' => 38,
                'codes'         => [['name' => 'Code', 'value' => $user->gender === 'm' ? 'MALE' : 'FEMALE']],
            ];
        }

        if ($user->nationality) {
            $input['nationality1'] = [
                'catalogueType' => 4,
                'codes'         => [['name' => 'Code', 'value' => $user->nationality]],
            ];
        }

        if ($user->birth_state) {
            $input['placeOfBirth'] = [
                'city'    => $user->birth_state,
                'country' => [
                    'catalogueType' => 0,
                    'codes'         => [['name' => 'ISO-Alpha2', 'value' => 'NG']],
                ],
            ];
        }

        if ($user->nin) {
            $input['externalIdType'] = [
                'catalogueType' => 22,
                'codes'         => [['name' => 'CODE', 'value' => 'nin']],
            ];
            $input['externalId']        = $user->nin;
            $input['socialInsuranceNo'] = $user->nin;
        }

        return $input;
    }

    private function buildAddressInput(int $customerNo, array $data): array
    {
        $input = [
            'customerNo' => $customerNo,
            'city'       => $data['city'],
            'country'    => [
                'catalogueType' => 0,
                'codes'         => [['name' => 'ISO-Alpha2', 'value' => 'NG']],
            ],
        ];

        $optional = ['street_name' => 'streetName', 'street_no' => 'streetNo', 'building' => 'building',
                     'extra_line'  => 'extraLine',  'region'    => 'region',   'zip_code'  => 'zipCode'];

        foreach ($optional as $key => $empicKey) {
            if (! empty($data[$key])) {
                $input[$empicKey] = $data[$key];
            }
        }

        return $input;
    }
}
