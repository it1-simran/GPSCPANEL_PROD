<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory;
    protected $table = 'permissions';
    protected $fillable = ['key', 'module', 'action', 'label', 'description', 'order', 'is_active'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    public function users()
    {
        return $this->belongsToMany(Writer::class, 'user_permissions');
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
