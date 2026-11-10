<?php

namespace App\Models\Relations;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

trait HasPermissionsTrait
{
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $userPermission = $this->userPermissions()->where('permission_id',
            Permission::where('slug', $permission)->first()?->id
        )->first();

        if ($userPermission) {
            return $userPermission->type === 'allow';
        }

        return false;
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getAllRolePermissions()
    {
        return Permission::whereHas('roles', function ($q) {
            $q->whereHas('users', function ($userQuery) {
                $userQuery->whereKey($this->getKey());
            });
        })->get();
    }
}
