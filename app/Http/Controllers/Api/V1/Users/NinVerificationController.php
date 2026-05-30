<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\NinVerificationRequest;
use App\Http\Resources\Users\NinVerifiedResource;
use App\Services\OneVerify\OneVerifyService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class NinVerificationController extends Controller
{
    public function __construct(private readonly OneVerifyService $oneVerify) {}

    /**
     * Submit a NIN for verification against the OneVERIFY IDENTITY API.
     * Populates the authenticated user's profile fields on success.
     */
    public function store(NinVerificationRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->nin_verified) {
            return $this->errorResponse('NIN is already verified for this account.', 409);
        }

        try {
            $data = $this->oneVerify->lookupNin($request->nin);
        } catch (RuntimeException $e) {
            Log::error('NIN verification failed', [
                'nin'   => $request->nin,
                'error' => $e->getMessage(),
            ]);
            return $this->errorResponse('NIN verification failed. Please try again later.', 422);
        }

      
        $user->fill([
            'nin'               => $data['nin']        ?? $request->nin,
            'first_name'        => $data['firstname']  ?? null,
            'last_name'         => $data['surname']    ?? null,
            'middle_name'       => $data['middlename'] ?? null,
            'date_of_birth'     => $this->parseBirthdate($data['birthdate'] ?? null),
            'gender'            => $this->normaliseGender($data['gender'] ?? null),
            'nin_photo'         => $data['photo']      ?? null,
            'nin_verified'      => true,
            'nin_verified_at'   => now(),
        ]);

        $user->save();

        return $this->successResponse([
            'success' => true,
            'message' => 'NIN verified successfully.',
            'data'    => new NinVerifiedResource($user),
        ], 200);
    }

    /**
     * Normalise the gender value from the API to the DB enum (m / f).
     */
    private function normaliseGender(?string $gender): ?string
    {
        if (is_null($gender)) {
            return null;
        }

        return match (strtolower(trim($gender))) {
            'male',   'm' => 'm',
            'female', 'f' => 'f',
            default        => null,
        };
    }

    /**
     * Parse birthdate from DD-MM-YYYY (OneVERIFY format) to a Carbon instance.
     */
    private function parseBirthdate(?string $birthdate): ?Carbon
    {
        if (is_null($birthdate)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('d-m-Y', $birthdate);
        } catch (\Throwable) {
            return null;
        }
    }
}
