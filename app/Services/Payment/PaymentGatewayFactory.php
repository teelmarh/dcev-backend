<?php

namespace App\Services\Payment;

use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\Gateways\PaystackGateway;
use App\Services\Payment\Gateways\RemitaGateway;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    public static function make(string $gateway): PaymentGatewayInterface
    {
        return match ($gateway) {
            'paystack' => new PaystackGateway(),
            'remita'   => new RemitaGateway(),
            default    => throw new InvalidArgumentException("Unsupported payment gateway: [{$gateway}]"),
        };
    }
}
