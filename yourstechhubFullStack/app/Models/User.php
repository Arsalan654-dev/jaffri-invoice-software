<?php

namespace App\Models;

use App\Models\Relations\HasPermissionsTrait;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone', 'employee_id', 'department', 'designation', 'is_active', 'must_change_password', 'timezone', 'locale'])]
#[Hidden(['password', 'remember_token', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPermissionsTrait, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_expires_at' => 'datetime',
            'two_factor_recovery_codes' => 'array',
            'last_activity_at' => 'datetime',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_users');
    }

    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    public function getEffectivePermissionsAttribute()
    {
        return $this->userPermissions()
            ->with('permission')
            ->get()
            ->filter(fn ($userPermission) => $userPermission->type === 'allow' && $userPermission->permission)
            ->pluck('permission')
            ->values();
    }
}
