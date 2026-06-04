<?php

namespace App\Http\Controllers\Api\V1\Empic;

use App\Http\Controllers\Controller;
use App\Jobs\Empic\SyncEmpicHumanJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmpicHumanController extends Controller
{
    /**
     * Dispatch the EMPIC createHuman job for the authenticated user.
     *
     * Guards:
     *  - User must have completed NIN verification (nin_verified = true)
     *  - User must not already have an empic_customer_no (idempotent — avoids duplicate CM records)
     */
    public function store(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->empic_customer_no) {
            return $this->errorResponse('This account is already registered in the EMPIC CM system.', 409);
        }

        SyncEmpicHumanJob::dispatch($user);

        return $this->showMessage('CM registration queued. You will be notified once it is complete.');
    }
}
