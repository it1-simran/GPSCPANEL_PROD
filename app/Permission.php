<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory;
    protected $table = 'permissions';
    protected $fillable = ['key', 'module', 'action', 'label', 'description', 'order', 'is_active', 'parent_permission_id'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    public function users()
    {
        return $this->belongsToMany(Writer::class, 'user_permissions');
    }

    /**
     * Get the parent permission (if this is a dependent permission)
     */
    public function parent()
    {
        return $this->belongsTo(Permission::class, 'parent_permission_id');
    }

    /**
     * Get all child permissions (dependent on this permission)
     */
    public function children()
    {
        return $this->hasMany(Permission::class, 'parent_permission_id');
    }

    /**
     * Get all dependent permissions (recursively)
     * @return array Array of permission IDs that depend on this permission
     */
    public function getDependentPermissionIds(): array
    {
        $dependentIds = [];

        foreach ($this->children as $child) {
            $dependentIds[] = $child->id;
            // Recursively get grandchildren
            $dependentIds = array_merge($dependentIds, $child->getDependentPermissionIds());
        }

        return $dependentIds;
    }

    /**
     * Check if this permission has a parent (is a dependent permission)
     */
    public function hasParent(): bool
    {
        return !is_null($this->parent_permission_id);
    }

    /**
     * Get the root parent permission
     */
    public function getRootParent(): ?Permission
    {
        if (!$this->parent_permission_id) {
            return $this;
        }

        return $this->parent->getRootParent();
    }

    public static function getByModule($module)
    {
        return self::where('module', $module)->where('is_active', 1)->get();
    }

    public static function getPermissionsByModule()
    {
        return self::where('is_active', 1)
            ->orderBy('module')
            ->orderBy('order')
            ->get()
            ->groupBy('module');
    }
}
