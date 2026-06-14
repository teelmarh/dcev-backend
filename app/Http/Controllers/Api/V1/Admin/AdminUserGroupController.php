<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Group\GroupUserRequest;
use App\Http\Requests\Admin\Group\StoreUserGroupRequest;
use App\Http\Requests\Admin\Group\SyncGroupPermissionsRequest;
use App\Http\Requests\Admin\Group\UpdateUserGroupRequest;
use App\Models\UserGroup;
use Illuminate\Http\JsonResponse;

class AdminUserGroupController extends Controller
{
    /**
     * GET /v1/admin/groups
     */
    public function index(): JsonResponse
    {
        $groups = UserGroup::with('permissions')->get();

        return $this->successResponse($groups->map(fn ($g) => $this->formatGroup($g)), 200);
    }

    /**
     * POST /v1/admin/groups
     */
    public function store(StoreUserGroupRequest $request): JsonResponse
    {
        $group = UserGroup::create($request->validated());

        return $this->successResponse($this->formatGroup($group->load('permissions')), 201, 'Group created.');
    }

    /**
     * GET /v1/admin/groups/{group}
     */
    public function show(int $group): JsonResponse
    {
        $group = UserGroup::with('permissions', 'users')->find($group);

        if (! $group) {
            return $this->errorResponse('Group not found.', 404);
        }

        return $this->successResponse($this->formatGroup($group), 200);
    }

    /**
     * PATCH /v1/admin/groups/{group}
     */
    public function update(UpdateUserGroupRequest $request, int $group): JsonResponse
    {
        $group = UserGroup::find($group);

        if (! $group) {
            return $this->errorResponse('Group not found.', 404);
        }

        $group->update($request->validated());

        return $this->successResponse($this->formatGroup($group->load('permissions')), 200, 'Group updated.');
    }

    /**
     * DELETE /v1/admin/groups/{group}
     */
    public function destroy(int $group): JsonResponse
    {
        $group = UserGroup::find($group);

        if (! $group) {
            return $this->errorResponse('Group not found.', 404);
        }

        $group->permissions()->detach();
        $group->users()->detach();
        $group->delete();

        return $this->showMessage('Group deleted.', 200);
    }

    /**
     * PUT /v1/admin/groups/{group}/permissions
     * Replace (sync) all permissions on a group.
     */
    public function syncPermissions(SyncGroupPermissionsRequest $request, int $group): JsonResponse
    {
        $group = UserGroup::find($group);

        if (! $group) {
            return $this->errorResponse('Group not found.', 404);
        }

        $group->permissions()->sync($request->permission_ids);

        return $this->successResponse($this->formatGroup($group->load('permissions')), 200, 'Permissions updated.');
    }

    /**
     * POST /v1/admin/groups/{group}/users
     * Add a user to the group.
     */
    public function addUser(GroupUserRequest $request, int $group): JsonResponse
    {
        $group = UserGroup::find($group);

        if (! $group) {
            return $this->errorResponse('Group not found.', 404);
        }

        $group->users()->syncWithoutDetaching([$request->user_id]);

        return $this->showMessage('User added to group.', 200);
    }

    /**
     * DELETE /v1/admin/groups/{group}/users/{user}
     * Remove a user from the group.
     */
    public function removeUser(int $group, int $user): JsonResponse
    {
        $group = UserGroup::find($group);

        if (! $group) {
            return $this->errorResponse('Group not found.', 404);
        }

        $group->users()->detach($user);

        return $this->showMessage('User removed from group.', 200);
    }

    private function formatGroup(UserGroup $group): array
    {
        return [
            'id'          => $group->id,
            'name'        => $group->name,
            'description' => $group->description,
            'active'      => $group->active,
            'permissions' => $group->permissions->map(fn ($p) => [
                'id'   => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
            ])->values(),
        ];
    }
}

