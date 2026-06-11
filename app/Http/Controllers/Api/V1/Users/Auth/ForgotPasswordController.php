<?php

namespace App\Http\Controllers\Api\V1\Users\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\ForgotPasswordFormRequest;
use App\Models\User;
use App\Notifications\User\ResetPasswordNotification;
use App\Traits\Api\OtpTraits;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ForgotPasswordController extends Controller
{
    use OtpTraits;

    /**
     * Forgot Password
     *
     * Forgot Password
     */
    
    public function store(ForgotPasswordFormRequest $request)
    {
        $otp = $this->generate_otp($request->email);

        $user = User::where('email', $request->email)->first();

        try {
            Notification::send($user, new ResetPasswordNotification($user, $otp?->token));
        } catch (\Throwable $e) {
            Log::error('Failed to send password reset email', ['email' => $request->email, 'error' => $e->getMessage()]);
            return $this->errorResponse('Our email service is temporarily unavailable. Please try again shortly.', 503);
        }

        return $this->showMessage('An OTP has been sent to your mail to rest your password');
    }
}
