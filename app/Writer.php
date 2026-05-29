<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Writer extends Authenticatable{
    use Notifiable;
    /**

     * @var string

    */
    protected $guard = 'writer';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device_category_id','configurations','can_configurations','name', 'mobile', 'email', 'password','LoginPassword','showLoginPassword','today_pings','total_pings','otp','twoFactorAuthentication','is_support_active','timezone','user_type','created_by','twoFactorAuthToken','two_factor_expires_at','role_id','parent_user_id'
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
    */
    protected $hidden = [
        'remember_token'
    ];
    public function devices()
    {
        return $this->hasMany(Device::class, 'user_id');
    }

    /**
     * User's Role relationship
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * User's Permissions relationship (for individual permission overrides)
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id');
    }

    /**
     * Parent User relationship (for hierarchy)
     */
    public function parentUser()
    {
        return $this->belongsTo(Writer::class, 'parent_user_id');
    }

    /**
     * Child Users relationship (for hierarchy)
     */
    public function childUsers()
    {
        return $this->hasMany(Writer::class, 'parent_user_id');
    }
}

