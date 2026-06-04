<?php

namespace App\Http\Controllers\Api\V1\Empic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Empic\AddAddressRequest;
use App\Jobs\Empic\SyncEmpicAddressJob;
use Illuminate\Http\JsonResponse;

class EmpicAddressController extends Controller
{
    /**
     * Dispatch the EMPIC addAddress job for the authenticated user.
     *
     * Guards:
     *  - User must have an empic_customer_no (CM human record must exist first)
     *  - User must not already have an empic_address_id
     */
    public function store(AddAddressRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->empic_customer_no) {
            return $this->errorResponse(
                'You must complete EMPIC CM registration before adding an address.',
                409
            );
        }

        if ($user->empic_address_id) {
            return $this->errorResponse('An address is already registered for this account in EMPIC CM.', 409);
        }

        SyncEmpicAddressJob::dispatch($user, $request->validated());

        return $this->showMessage('Address registration queued. You will be notified once it is complete.');
    }
}
