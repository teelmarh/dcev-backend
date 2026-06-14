<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Officer\DestroyOfficerRequest;
use App\Http\Requests\Admin\Officer\ShowOfficerRequest;
use App\Http\Requests\Admin\Officer\StoreOfficerRequest;
use App\Http\Requests\Admin\Officer\UpdateOfficerRequest;
use App\Http\Resources\Users\UserResource;
use App\Models\User;
use App\Notifications\User\OfficerWelcomeNotification;
use App\Traits\Api\OtpTraits;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminOfficerController extends Controller
{
    use OtpTraits;

    /** GET /v1/admin/officers */
    public function index(Request $request): JsonResponse
    {
        $officers = User::whereIn('role', ['officer', 'superadmin'])
            ->with('regionalOffice')
            ->when($request->query('office_id'), fn ($q, $id) => $q->where('regional_office_id', $id))
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate(20);

        return $this->successResponse(
            UserResource::collection($officers)->response()->getData(true),
            200,
            'Officers retrieved.'
        );
    }

    /** GET /v1/admin/officers/show */
    public function show(ShowOfficerRequest $request): JsonResponse
    {
        $officer = User::whereIn('role', ['officer', 'superadmin'])
            ->with('regionalOffice', 'userGroups.permissions', 'directPermissions')
            ->find($request->officer_id);

        if (! $officer) {
            return $this->errorResponse('Officer not found.', 404);
        }

        return $this->successResponse(new UserResource($officer), 200, 'Officer retrieved.');
    }

    /**
     * POST /v1/admin/officers
     * Creates a new officer/superadmin account and sends a 6-hour OTP to set their password.
     */
    public function store(StoreOfficerRequest $request): JsonResponse
    {
        $officer = User::create([
            'first_name'         => $request->first_name,
            'last_name'          => $request->last_name,
            'email'              => $request->email,
            'phone'              => $request->phone,
            'password'           => bcrypt(uniqid('', true)), // placeholder — replaced via OTP flow
            'role'               => $request->input('role', 'officer'),
            'regional_office_id' => $request->regional_office_id,
            'email_verified_at'  => now(),
        ]);

        $otp = $this->generate_otp($officer->email, 360); // 6 hours

        try {
            $officer->notify(new OfficerWelcomeNotification($officer, $otp->token));
        } catch (\Throwable $e) {
            Log::error('Failed to send officer welcome email', [
                'officer_id' => $officer->id,
                'error'      => $e->getMessage(),
            ]);
        }

        return $this->successResponse(
            new UserResource($officer->load('regionalOffice')),
            201,
            'Officer account created. A password setup OTP has been sent to their email.'
        );
    }

    /**
     * PATCH /v1/admin/officers
     * Update an officer's assigned regional office.
     */
    public function update(UpdateOfficerRequest $request): JsonResponse
    {
        $officer = User::whereIn('role', ['officer', 'superadmin'])->find($request->officer_id);

        if (! $officer) {
            return $this->errorResponse('Officer not found.', 404);
        }

        $officer->update(['regional_office_id' => $request->regional_office_id]);

        return $this->successResponse(
            new UserResource($officer->load('regionalOffice')),
            200,
            'Officer regional office updated.'
        );
    }

    /**
     * DELETE /v1/admin/officers
     * Permanently deletes an officer account.
     */
    public function destroy(DestroyOfficerRequest $request): JsonResponse
    {
        $officer = User::whereIn('role', ['officer', 'superadmin'])->find($request->officer_id);

        if (! $officer) {
            return $this->errorResponse('Officer not found.', 404);
        }

        $officer->userGroups()->detach();
        $officer->directPermissions()->detach();
        $officer->tokens()->delete();
        $officer->delete();

        return $this->showMessage('Officer account permanently deleted.', 200);
    }
}

