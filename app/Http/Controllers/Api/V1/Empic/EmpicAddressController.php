<?php

namespace App\Http\Controllers\Api\V1\Empic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Empic\AddAddressRequest;
use App\Jobs\Empic\SyncEmpicAddressJob;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class EmpicAddressController extends Controller
{
    /**
     * Queue EMPIC address registration for a specified user (admin action).
     *
     * Guards:
     *  - Target user must have an empic_customer_no (human record must exist first)
     *  - Target user must not already have an empic_address_id
     */
    public function store(AddAddressRequest $request): JsonResponse
    {
        $user = User::findOrFail($request->validated('user_id'));

        if (! $user->empic_customer_no) {
            return $this->errorResponse(
                'Complete EMPIC CM human registration before adding an address.',
                409
            );
        }

        if ($user->empic_address_id) {
            return $this->errorResponse('An address is already registered for this account in EMPIC CM.', 409);
        }

        $user->update(['empic_status' => 'pending']);

        $addressData = collect($request->validated())->except('user_id')->all();

        SyncEmpicAddressJob::dispatch($user->id, $addressData);

        return $this->showMessage('Address registration queued.');
    }
}
