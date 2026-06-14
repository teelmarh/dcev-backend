<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Officer\OfficerPermissionRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOfficerPermissionController extends Controller
{
    /**
     * GET /v1/admin/officers/permissions?officer_id=X
     * List all resolved permissions for an officer (group-assigned + direct).
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'officer_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $officer = User::whereIn('role', ['officer', 'superadmin'])
            ->with(['userGroups.permissions', 'directPermissions'])
            ->find($request->officer_id);

        if (! $officer) {
            return $this->errorResponse('Officer not found.', 404);
        }

        $groupPermissions = $officer->userGroups->flatMap(
            fn ($g) => $g->permissions->map(fn ($p) => [
                'id'     => $p->id,
                'name'   => $p->name,
                'slug'   => $p->slug,
                'source' => 'group:' . $g->name,
            ])
        );

        $directPermissions = $officer->directPermissions->map(fn ($p) => [
            'id'     => $p->id,
            'name'   => $p->name,
            'slug'   => $p->slug,
            'source' => 'direct',
        ]);

        return $this->successResponse(
            $groupPermissions->merge($directPermissions)->unique('id')->values(),
            200,
            'Permissions retrieved.'
        );
    }

    /**
     * POST /v1/admin/officers/permissions
     * Grant a direct permission to an officer.
     * Body: officer_id, permission_id
     */
    public function store(OfficerPermissionRequest $request): JsonResponse
    {
        $officer = User::whereIn('role', ['officer', 'superadmin'])->find($request->officer_id);

        if (! $officer) {
            return $this->errorResponse('Officer not found.', 404);
        }

        $officer->directPermissions()->syncWithoutDetaching([$request->permission_id]);

        return $this->showMessage('Permission granted.', 200);
    }

    /**
     * DELETE /v1/admin/officers/permissions
     * Revoke a direct permission from an officer.
     * Body: officer_id, permission_id
     */
    public function destroy(OfficerPermissionRequest $request): JsonResponse
    {
        $officer = User::whereIn('role', ['officer', 'superadmin'])->find($request->officer_id);

        if (! $officer) {
            return $this->errorResponse('Officer not found.', 404);
        }

        $officer->directPermissions()->detach($request->permission_id);

        return $this->showMessage('Permission revoked.', 200);
    }
}
