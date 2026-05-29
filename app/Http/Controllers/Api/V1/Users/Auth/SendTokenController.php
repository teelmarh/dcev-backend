<?php

namespace App\Http\Controllers\Api\V1\Users\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\Api\OtpTraits;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\User\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\User\VerifyEmailNotification;
use App\Http\Requests\User\Auth\CreateVerificationTokenRequest;

class SendTokenController extends Controller
{
    use OtpTraits;
    
    public function store(CreateVerificationTokenRequest $request)
    {

        $user = User::where('email', $request->email)->first();

        $otp = $this->generate_otp($user->email);

        Notification::send($user, new VerifyEmailNotification($user, $otp?->token));
        
        return $this->showMessage('An OTP has been sent to your email', 200);


    }
}
