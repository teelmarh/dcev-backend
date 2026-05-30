<?php

namespace App\Http\Controllers\Api\V1\Users\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\VerificationFormRequest;
use App\Models\User;
use App\Traits\Api\OtpTraits;

class VerifyEmailController extends Controller
{
    use OtpTraits;

    /**
     * User Email Verification
     *
     * User Email Verification
     */
    public function store(VerificationFormRequest $request)
    {
        $user = User::where(['email' => $request['email']])->first();

        $response = $this->validate_otp($request->validated());

        if (!$response->status) {
            return $this->errorResponse('OTP does not exist. Please resend a mail to verify your email', 409);
        }

        if (!is_null($user->email_verified_at)) {
            return $this->errorResponse('Your emails is already verified', 409);
        }

        $user->email_verified_at = now();

        $user->save();

        return $this->showMessage('Email Verified');
    }
}
