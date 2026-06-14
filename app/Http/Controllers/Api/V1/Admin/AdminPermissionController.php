<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;

class AdminPermissionController extends Controller
{
    /**
     * GET /v1/admin/permissions
     * Return all available permissions.
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::orderBy('slug')->get(['id', 'name', 'slug']);

        return $this->successResponse($permissions, 200, 'Permissions retrieved.');
    }
}
