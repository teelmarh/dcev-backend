<?php

namespace App\Http\Controllers\Api\V1\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transactions\InitiateDeliveryRequest;
use App\Http\Requests\Transactions\InitiateEnrollmentRequest;
use App\Http\Requests\Transactions\VerifyTransactionRequest;
use App\Http\Resources\Transactions\TransactionResource;
use App\Models\Licence;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\PaymentGatewayFactory;
use App\Services\Payment\PaymentService;
use App\Traits\Api\ApiResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use ApiResponder;

    public function __construct(private PaymentService $paymentService) {}

    /**
     * POST /v1/transactions/enrollment/initiate
     */
    public function initiateEnrollment(InitiateEnrollmentRequest $request): JsonResponse
    {
        $transaction = $this->paymentService->initiateEnrollment(
            $request->user(),
            $request->validated('gateway')
        );

        return $this->dataResponse(
            new TransactionResource($transaction),
            'Enrollment payment initiated.',
            true,
            201
        );
    }

    /**
     * POST /v1/transactions/enrollment/verify
     */
    public function verifyEnrollment(VerifyTransactionRequest $request): JsonResponse
    {
        $transaction = $this->paymentService->verify($request->validated('reference'));

        return $this->dataResponse(
            new TransactionResource($transaction),
            $transaction->isPaid() ? 'Payment confirmed.' : 'Payment not yet confirmed.',
            $transaction->isPaid(),
            200
        );
    }

    /**
     * POST /v1/transactions/delivery/initiate
     */
    public function initiateDelivery(InitiateDeliveryRequest $request): JsonResponse
    {
        $licence = Licence::findOrFail($request->validated('licence_id'));

        // Ownership check
        if ($licence->user_id !== $request->user()->id) {
            return $this->dataResponse(null, 'Forbidden.', false, 403);
        }

        if ($licence->delivery_method !== 'delivery') {
            return $this->dataResponse(null, 'This licence does not require a delivery payment.', false, 422);
        }

        $transaction = $this->paymentService->initiateDelivery(
            $licence,
            $request->validated('gateway')
        );

        return $this->dataResponse(
            new TransactionResource($transaction),
            'Delivery payment initiated.',
            true,
            201
        );
    }

    /**
     * POST /v1/transactions/delivery/verify
     */
    public function verifyDelivery(VerifyTransactionRequest $request): JsonResponse
    {
        $transaction = $this->paymentService->verify($request->validated('reference'));

        return $this->dataResponse(
            new TransactionResource($transaction),
            $transaction->isPaid() ? 'Delivery payment confirmed.' : 'Payment not yet confirmed.',
            $transaction->isPaid(),
            200
        );
    }
}
