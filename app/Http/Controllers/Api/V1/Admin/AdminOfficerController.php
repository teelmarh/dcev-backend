<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Officer\StoreOfficerRequest;
use App\Http\Requests\Admin\Officer\UpdateOfficerRequest;
use App\Http\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminOfficerController extends Controller
{
    /**
     * GET /v1/admin/officers
     */
    public function index(Request $request): JsonResponse
    {
        $officers = User::where('role', 'officer')
            ->with('regionalOffice')
            ->when($request->query('office_id'), fn ($q, $id) => $q->where('regional_office_id', $id))
            ->paginate(20);

        return $this->successResponse(
            UserResource::collection($officers)->response()->getData(true),
            200
        );
    }

    /**
     * POST /v1/admin/officers
     * Create a new officer account with a system-generated default password.
     */
    public function store(StoreOfficerRequest $request): JsonResponse
    {
        $defaultPassword = 'Officer@' . Str::upper(Str::random(6));

        $officer = User::create([
            'first_name'         => $request->first_name,
            'last_name'          => $request->last_name,
            'email'              => $request->email,
            'phone'              => $request->phone,
            'password'           => Hash::make($defaultPassword),
            'role'               => 'officer',
            'regional_office_id' => $request->regional_office_id,
            'email_verified_at'  => now(),
        ]);

        return $this->successResponse([
            'officer'          => new UserResource($officer->load('regionalOffice')),
            'default_password' => $defaultPassword,
        ], 201, 'Officer account created.');
    }

    /**
     * GET /v1/admin/officers/{officer}
     */
    public function show(int $officer): JsonResponse
    {
        $officer = User::where('role', 'officer')->with('regionalOffice', 'userGroups.permissions', 'directPermissions')->find($officer);

        if (! $officer) {
            return $this->errorResponse('Officer not found.', 404);
        }

        return $this->successResponse(new UserResource($officer), 200);
    }

    /**
     * PATCH /v1/admin/officers/{officer}
     * Update an officer's assigned office.
     */
    public function update(UpdateOfficerRequest $request, int $officer): JsonResponse
    {
        $officer = User::where('role', 'officer')->find($officer);

        if (! $officer) {
            return $this->errorResponse('Officer not found.', 404);
        }

        $officer->update(['regional_office_id' => $request->regional_office_id]);

        return $this->successResponse(new UserResource($officer->load('regionalOffice')), 200, 'Officer updated.');
    }

    /**
     * DELETE /v1/admin/officers/{officer}
     * Permanently delete the officer account.
     */
    public function destroy(int $officer): JsonResponse
    {
        $officer = User::where('role', 'officer')->find($officer);

        if (! $officer) {
            return $this->errorResponse('Officer not found.', 404);
        }

        $officer->userGroups()->detach();
        $officer->directPermissions()->detach();
        $officer->tokens()->delete();
        $officer->delete();

        return $this->showMessage('Officer account deleted.', 200);
    }
}
