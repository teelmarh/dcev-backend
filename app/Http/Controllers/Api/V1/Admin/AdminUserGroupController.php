<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Group\DestroyGroupRequest;
use App\Http\Requests\Admin\Group\GroupUserRequest;
use App\Http\Requests\Admin\Group\ShowGroupRequest;
use App\Http\Requests\Admin\Group\StoreUserGroupRequest;
use App\Http\Requests\Admin\Group\SyncGroupPermissionsRequest;
use App\Http\Requests\Admin\Group\UpdateUserGroupRequest;
use App\Models\UserGroup;
use Illuminate\Http\JsonResponse;

class AdminUserGroupController extends Controller
{
    /** GET /v1/admin/groups */
    public function index(): JsonResponse
    {
        $groups = UserGroup::with('permissions')->get();

        return $this->successResponse($groups->map(fn ($g) => $this->formatGroup($g)), 200, 'Groups retrieved.');
    }

    /** POST /v1/admin/groups */
    public function store(StoreUserGroupRequest $request): JsonResponse
    {
        $group = UserGroup::create($request->validated());

        return $this->successResponse($this->formatGroup($group->load('permissions')), 201, 'Group created.');
    }

    /** GET /v1/admin/groups/show */
    public function show(ShowGroupRequest $request): JsonResponse
    {
        $group = UserGroup::with('permissions', 'users')->find($request->group_id);

        if (! $group) {
            return $this->errorResponse('Group not found.', 404);
        }

        return $this->successResponse($this->formatGroup($group), 200, 'Group retrieved.');
    }

    /** PATCH /v1/admin/groups */
    public function update(UpdateUserGroupRequest $request): JsonResponse
    {
        $group = UserGroup::find($request->group_id);

        if (! $group) {
            return $this->errorResponse('Group not found.', 404);
        }

        $group->update(collect($request->validated())->except('group_id')->toArray());

        return $this->successResponse($this->formatGroup($group->load('permissions')), 200, 'Group updated.');
    }

    /** DELETE /v1/admin/groups */
    public function destroy(DestroyGroupRequest $request): JsonResponse
    {
        $group = UserGroup::find($request->group_id);

        if (! $group) {
            return $this->errorResponse('Group not found.', 404);
        }

        $group->permissions()->detach();
        $group->users()->detach();
        $group->delete();

        return $this->showMessage('Group deleted.', 200);
    }

    /**
     * PUT /v1/admin/groups/permissions
     * Replace (sync) all permissions on a group.
     * Body: group_id, permission_ids[]
     */
    public function syncPermissions(SyncGroupPermissionsRequest $request): JsonResponse
    {
        $group = UserGroup::find($request->group_id);

        if (! $group) {
            return $this->errorResponse('Group not found.', 404);
        }

        $group->permissions()->sync($request->permission_ids);

        return $this->successResponse($this->formatGroup($group->load('permissions')), 200, 'Group permissions updated.');
    }

    /**
     * POST /v1/admin/groups/users
     * Add a user to the group.
     * Body: group_id, user_id
     */
    public function addUser(GroupUserRequest $request): JsonResponse
    {
        $group = UserGroup::find($request->group_id);

        if (! $group) {
            return $this->errorResponse('Group not found.', 404);
        }

        $group->users()->syncWithoutDetaching([$request->user_id]);

        return $this->showMessage('User added to group.', 200);
    }

    /**
     * DELETE /v1/admin/groups/users
     * Remove a user from the group.
     * Body: group_id, user_id
     */
    public function removeUser(GroupUserRequest $request): JsonResponse
    {
        $group = UserGroup::find($request->group_id);

        if (! $group) {
            return $this->errorResponse('Group not found.', 404);
        }

        $group->users()->detach($request->user_id);

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


