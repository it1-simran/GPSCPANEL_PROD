<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'writers';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'mobile','email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the user's role
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get the user's permissions
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id');
    }

    /**
     * Get parent user (for hierarchical access)
     */
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    /**
     * Get child users
     */
    public function children()
    {
        return $this->hasMany(User::class, 'parent_user_id');
    }

    /**
     * Get all descendants recursively
     */
    public function allDescendants()
    {
        $descendants = collect();
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->allDescendants());
        }
        return $descendants;
    }

    /**
     * Check if user has a specific permission
     *
     * @param string $permissionKey
     * @return bool
     */
    public function hasPermission($permissionKey)
    {
        return \App\Helpers\PermissionHelper::hasPermission($permissionKey, $this);
    }

    /**
     * Check if user can manage another user (is their parent or ancestor)
     *
     * @param User $targetUser
     * @return bool
     */
    public function canManage(User $targetUser): bool
    {
        // Admin can manage everyone
        if ($this->user_type === 'Admin') {
            return true;
        }

        // Check if targetUser is a descendant
        return $this->allDescendants()->contains($targetUser);
    }

    /**
     * Get assignable permissions (permissions this user can assign to others)
     * Child can only have permissions that parent has
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAssignablePermissions()
    {
        return $this->permissions()->where('is_active', 1)->get();
    }
}
