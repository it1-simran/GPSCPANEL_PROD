<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    const ADMIN = 'Admin';
    const RESELLER = 'Reseller';
    const USER = 'User';
    const DEALER = 'Dealer';

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function users()
    {
        return $this->hasMany(Writer::class, 'role_id');
    }

    public static function getAdminRole()
    {
        return self::where('slug', 'admin')->first();
    }

    public static function getResellerRole()
    {
        return self::where('slug', 'reseller')->first();
    }

    public static function getUserRole()
    {
        return self::where('slug', 'user')->first();
    }
}
