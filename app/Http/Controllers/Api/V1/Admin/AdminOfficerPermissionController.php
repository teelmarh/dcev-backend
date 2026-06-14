<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Officer\OfficerPermissionRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminOfficerPermissionController extends Controller
{
    /**
     * GET /v1/admin/officers/{officer}/permissions
     * List all resolved permissions for an officer (groups + direct).
     */
    public function index(int $officer): JsonResponse
    {
        $officer = User::where('role', 'officer')
            ->with(['userGroups.permissions', 'directPermissions'])
            ->find($officer);

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
            200
        );
    }

    /**
     * POST /v1/admin/officers/{officer}/permissions
     * Grant a direct permission to an officer.
     */
    public function store(OfficerPermissionRequest $request, int $officer): JsonResponse
    {
        $officer = User::where('role', 'officer')->find($officer);

        if (! $officer) {
            return $this->errorResponse('Officer not found.', 404);
        }

        $officer->directPermissions()->syncWithoutDetaching([$request->permission_id]);

        return $this->showMessage('Permission granted.', 200);
    }

    /**
     * DELETE /v1/admin/officers/{officer}/permissions/{permission}
     * Revoke a direct permission from an officer.
     */
    public function destroy(int $officer, int $permission): JsonResponse
    {
        $officer = User::where('role', 'officer')->find($officer);

        if (! $officer) {
            return $this->errorResponse('Officer not found.', 404);
        }

        $officer->directPermissions()->detach($permission);

        return $this->showMessage('Permission revoked.', 200);
    }
}
