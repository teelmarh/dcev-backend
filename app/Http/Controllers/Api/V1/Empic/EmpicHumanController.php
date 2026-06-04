<?php

namespace App\Http\Controllers\Api\V1\Empic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Empic\EmpicHumanRequest;
use App\Jobs\Empic\SyncEmpicHumanJob;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class EmpicHumanController extends Controller
{
    /**
     * Queue CM human registration for a specified user (admin action).
     *
     * Guards:
     *  - Target user must have completed NIN verification
     *  - Target user must not already have an empic_customer_no
     */
    public function store(EmpicHumanRequest $request): JsonResponse
    {
        $user = User::findOrFail($request->validated('user_id'));

        if (! $user->nin_verified) {
            return $this->errorResponse('NIN verification must be completed before registering with EMPIC.', 422);
        }

        if ($user->empic_customer_no) {
            return $this->errorResponse('This account is already registered in the EMPIC CM system.', 409);
        }

        $user->update(['empic_status' => 'pending']);

        SyncEmpicHumanJob::dispatch($user->id);

        return $this->showMessage('CM human registration queued.');
    }
}
