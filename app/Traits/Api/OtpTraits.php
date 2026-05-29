<?php

namespace App\Traits\Api;

use Ichtrojan\Otp\Otp;

trait OtpTraits
{
    private $length = 6; // lenght digit to generate

    private $validity_period = 10; // validity period in minute

    protected function generate_otp($email)
    {
        $otpobj = new Otp;
        $otp = $otpobj->generate($email, 'numeric', (int) $this->length, $this->validity_period);

        return $otp;
    }

    protected function validate_otp($detail)
    {
        $otpobj = new Otp;

        $response = $otpobj->validate($detail['email'], $detail['otp']);

        return $response;
    }
}
