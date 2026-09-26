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
        'device_category_id','configurations','can_configurations','name', 'mobile', 'email', 'password','LoginPassword','showLoginPassword','today_pings','total_pings','otp','twoFactorAuthentication','is_support_active','timezone','user_type','created_by','twoFactorAuthToken','two_factor_expires_at','role_id','parent_user_id',
        'organization_name','gstin','pan_number','organization_address','kyc_document_path','kyc_status','kyc_rejection_reason','kyc_reviewed_by','kyc_reviewed_at','kyc_submitted_at',
    ];

    protected $casts = [
        'kyc_submitted_at' => 'datetime',
        'kyc_reviewed_at' => 'datetime',
    ];

    /**
     * KYC must be Approved before this account may raise a Purchase Order.
     */
    public function isKycApproved(): bool
    {
        return $this->kyc_status === 'Approved';
    }

    /**
     * "Level 1" = created directly under Admin (parent_user_id points at the
     * Admin account, or there's no parent at all). Sub-accounts created by a
     * Reseller/User (parent_user_id points at a non-Admin account) are
     * "Level 2+" and don't get PO/KYC/SKU of their own — those stay owned by
     * whichever Level 1 account created them.
     */
    public function isLevel1(): bool
    {
        if (!$this->parent_user_id) {
            return true;
        }
        if ((int) $this->parent_user_id === (int) $this->id) {
            return true;
        }
        $parent = self::find($this->parent_user_id);
        return !$parent || $parent->user_type === 'Admin';
    }
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
}

