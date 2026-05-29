<?php

namespace App\Http\Controllers\Api\V1\Users\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\ForgotPasswordFormRequest;
use App\Models\User;
use App\Notifications\User\ResetPasswordNotification;
use App\Traits\Api\OtpTraits;
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

        Notification::send($user, new ResetPasswordNotification($user, $otp?->token));

        return $this->showMessage('An OTP has been sent to your mail to rest your password');
    }
}
