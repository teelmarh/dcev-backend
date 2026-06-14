<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\UserResource;
use App\Models\Permission;
use App\Models\RegionalOffice;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminOfficerController extends Controller
{
    /**
     * GET /v1/admin/officers
     * List all officers.
     */
    public function index(Request $request): JsonResponse
    {
        $officers = User::whereIn('role', ['officer', 'superadmin'])
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
     * Promote an existing user to officer and assign an office.
     * Body: user_id, regional_office_id
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id'            => ['required', 'integer', 'exists:users,id'],
            'regional_office_id' => ['required', 'integer', 'exists:regional_offices,id'],
        ]);

        $user = User::find($data['user_id']);

        if ($user->role === 'superadmin') {
            return $this->errorResponse('Cannot modify a superadmin account.', 422);
        }

        $user->update([
            'role'               => 'officer',
            'regional_office_id' => $data['regional_office_id'],
        ]);

        return $this->successResponse(new UserResource($user->fresh('regionalOffice')), 200, 'User promoted to officer.');
    }

    /**
     * PATCH /v1/admin/officers
     * Update an officer's assigned office.
     * Body: user_id, regional_office_id
     */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id'            => ['required', 'integer', 'exists:users,id'],
            'regional_office_id' => ['required', 'integer', 'exists:regional_offices,id'],
        ]);

        $user = User::find($data['user_id']);

        if ($user->role !== 'officer') {
            return $this->errorResponse('User is not an officer.', 422);
        }

        $user->update(['regional_office_id' => $data['regional_office_id']]);

        return $this->successResponse(new UserResource($user->fresh('regionalOffice')), 200, 'Officer office updated.');
    }

    /**
     * DELETE /v1/admin/officers
     * Revoke officer role, return user to applicant.
     * Body: user_id
     */
    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::find($data['user_id']);

        if ($user->role !== 'officer') {
            return $this->errorResponse('User is not an officer.', 422);
        }

        $user->update([
            'role'               => 'applicant',
            'regional_office_id' => null,
        ]);

        // detach all group memberships
        $user->userGroups()->detach();

        return $this->showMessage('Officer role revoked.', 200);
    }

    /**
     * POST /v1/admin/officers/permissions
     * Assign a direct permission to an officer.
     * Body: user_id, permission_id
     */
    public function grantPermission(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id'       => ['required', 'integer', 'exists:users,id'],
            'permission_id' => ['required', 'integer', 'exists:permissions,id'],
        ]);

        $user = User::find($data['user_id']);
        $user->directPermissions()->syncWithoutDetaching([$data['permission_id']]);

        return $this->showMessage('Permission granted.', 200);
    }

    /**
     * DELETE /v1/admin/officers/permissions
     * Revoke a direct permission from an officer.
     * Body: user_id, permission_id
     */
    public function revokePermission(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id'       => ['required', 'integer', 'exists:users,id'],
            'permission_id' => ['required', 'integer', 'exists:permissions,id'],
        ]);

        $user = User::find($data['user_id']);
        $user->directPermissions()->detach($data['permission_id']);

        return $this->showMessage('Permission revoked.', 200);
    }
}
