<?php

namespace App\Traits;

use App\Models\Permission;
use Illuminate\Support\Collection;

trait HasPermissions
{
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Check if the user has a given permission by slug.
     * Superadmin always returns true.
     * Otherwise: union of all group permissions + direct user permissions.
     */
    public function hasPermission(string $slug): bool
    {
        if ($this->role === 'superadmin') {
            return true;
        }

        return $this->resolvedPermissionSlugs()->contains($slug);
    }

    /**
     * Return a flat collection of all permission slugs available to this user.
     * Result is cached on the instance to avoid repeat queries per request.
     */
    public function resolvedPermissionSlugs(): Collection
    {
        if (isset($this->_resolvedPermissions)) {
            return $this->_resolvedPermissions;
        }

        $groupSlugs = $this->userGroups()
            ->where('active', true)
            ->with('permissions')
            ->get()
            ->flatMap(fn ($group) => $group->permissions->pluck('slug'));

        $directSlugs = $this->directPermissions()->pluck('slug');

        $this->_resolvedPermissions = $groupSlugs->merge($directSlugs)->unique()->values();

        return $this->_resolvedPermissions;
    }

    /** Flush the cached permission slugs (call after group/permission changes). */
    public function flushPermissionCache(): void
    {
        unset($this->_resolvedPermissions);
    }
}
