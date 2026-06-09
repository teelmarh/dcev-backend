<?php

namespace App\Http\Controllers\Api\V1\Transactions;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentGatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
    /**
     * POST /v1/webhooks/{gateway}
     * No auth:sanctum — validated by gateway signature inside each handler.
     */
    public function handle(Request $request, string $gateway): Response
    {
        $handler = PaymentGatewayFactory::make($gateway);
        $handler->handleWebhook($request);

        return response('OK', 200);
    }
}
