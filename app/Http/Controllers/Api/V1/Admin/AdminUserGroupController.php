<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserGroup;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:user_groups,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'active'      => ['sometimes', 'boolean'],
        ]);

        $group = UserGroup::create($data);

        return $this->successResponse($this->formatGroup($group->load('permissions')), 201, 'Group created.');
    }

    /**
     * PATCH /v1/admin/groups
     * Body: group_id, name?, description?, active?
     */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'group_id'    => ['required', 'integer', 'exists:user_groups,id'],
            'name'        => ['sometimes', 'string', 'max:100', Rule::unique('user_groups', 'name')->ignore($request->group_id)],
            'description' => ['nullable', 'string', 'max:255'],
            'active'      => ['sometimes', 'boolean'],
        ]);

        $group = UserGroup::find($data['group_id']);
        $group->update(collect($data)->except('group_id')->toArray());

        return $this->successResponse($this->formatGroup($group->load('permissions')), 200, 'Group updated.');
    }

    /**
     * POST /v1/admin/groups/permissions
     * Sync permissions on a group.
     * Body: group_id, permission_ids[]
     */
    public function syncPermissions(Request $request): JsonResponse
    {
        $data = $request->validate([
            'group_id'       => ['required', 'integer', 'exists:user_groups,id'],
            'permission_ids' => ['required', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $group = UserGroup::find($data['group_id']);
        $group->permissions()->sync($data['permission_ids']);

        return $this->successResponse($this->formatGroup($group->load('permissions')), 200, 'Permissions updated.');
    }

    /**
     * POST /v1/admin/groups/users
     * Add a user to a group.
     * Body: group_id, user_id
     */
    public function addUser(Request $request): JsonResponse
    {
        $data = $request->validate([
            'group_id' => ['required', 'integer', 'exists:user_groups,id'],
            'user_id'  => ['required', 'integer', 'exists:users,id'],
        ]);

        $group = UserGroup::find($data['group_id']);
        $group->users()->syncWithoutDetaching([$data['user_id']]);

        return $this->showMessage('User added to group.', 200);
    }

    /**
     * DELETE /v1/admin/groups/users
     * Remove a user from a group.
     * Body: group_id, user_id
     */
    public function removeUser(Request $request): JsonResponse
    {
        $data = $request->validate([
            'group_id' => ['required', 'integer', 'exists:user_groups,id'],
            'user_id'  => ['required', 'integer', 'exists:users,id'],
        ]);

        $group = UserGroup::find($data['group_id']);
        $group->users()->detach($data['user_id']);

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
