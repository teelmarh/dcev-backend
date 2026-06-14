<?php

namespace App\Traits\Api;

use Ichtrojan\Otp\Otp;

trait OtpTraits
{
    private int $length = 6;

    private int $validity_period = 10; // minutes

    protected function generate_otp(string $email, int $validityMinutes = 0): object
    {
        $otpobj = new Otp;

        return $otpobj->generate(
            $email,
            'numeric',
            $this->length,
            $validityMinutes > 0 ? $validityMinutes : $this->validity_period
        );
    }

    protected function validate_otp(array $detail): object
    {
        $otpobj = new Otp;

        return $otpobj->validate($detail['email'], $detail['otp']);
    }
}

