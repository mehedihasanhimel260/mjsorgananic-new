<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admins';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function conversions()
    {
        return $this->hasMany(Conversion::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'admin_roles')->with('permissions')->withTimestamps();
    }

    public function permissions()
    {
        return $this->roles->flatMap->permissions->unique('id')->values();
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if (! $this->relationLoaded('roles')) {
            $this->load('roles.permissions');
        }

        if ($this->roles->isEmpty()) {
            return true;
        }

        return $this->permissions()->contains(fn ($permission) => $permission->slug === $permissionSlug);
    }

    public function hasRole(string $roleSlug): bool
    {
        if (! $this->relationLoaded('roles')) {
            $this->load('roles');
        }

        return $this->roles->contains(fn ($role) => $role->slug === $roleSlug);
    }
}
