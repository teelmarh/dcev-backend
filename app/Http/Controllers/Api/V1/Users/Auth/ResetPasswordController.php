<?php

namespace App\Http\Controllers\Api\V1\Users\Auth;

use App\Models\User;
use App\Services\AuditLogger;
use App\Traits\Api\OtpTraits;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\ResetPasswordRequest;

class ResetPasswordController extends Controller
{
    use OtpTraits;

    /**
     * User Reset Password with OTP
     *
     * User Reset Password with OTP
     */
    public function store(ResetPasswordRequest $request)
    {
        $user = User::where(['email' => $request['email']])->first();

        if (! $user) {
            return $this->errorResponse('User not found', 404);
        }

        $response = $this->validate_otp($request->validated());

        if (! $response->status) {
            return $this->errorResponse('Invalid Token', 409);
        }

        User::where('email', $request['email'])->update(['password' => bcrypt($request['password'])]);

        AuditLogger::log($user, AuditLogger::PASSWORD_RESET, $user, [], $request);

        return $this->showMessage('Your password has been changed! . Please login with your email and new password', 200);
    }
}
