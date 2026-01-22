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
        'device_category_id','configurations','can_configurations','name', 'mobile', 'email', 'password','LoginPassword','showLoginPassword','today_pings','total_pings','otp','twoFactorAuthentication','is_support_active','timezone','user_type','created_by','twoFactorAuthToken','two_factor_expires_at'
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
}

