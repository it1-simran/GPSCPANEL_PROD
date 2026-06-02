<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PermissionAuditLog extends Model
{
    protected $table = 'permission_audit_logs';
    protected $fillable = [
        'user_id',
        'permission_id',
        'assigned_by',
        'action',
        'reason',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that the permission was assigned/revoked to
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the permission that was assigned/revoked
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    /**
     * Get the admin/reseller who made the change
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Log a permission assignment or revocation
     *
     * @param User $targetUser
     * @param Permission $permission
     * @param string $action 'assigned' or 'revoked'
     * @param User|null $assignedBy
     * @param string|null $reason
     * @param array|null $metadata
     * @return static
     */
    public static function log(
        User $targetUser,
        Permission $permission,
        string $action = 'assigned',
        User $assignedBy = null,
        string $reason = null,
        array $metadata = null
    ) {
        if (!$assignedBy) {
            $assignedBy = auth()->user();
        }

        return static::create([
            'user_id' => $targetUser->id,
            'permission_id' => $permission->id,
            'assigned_by' => $assignedBy->id,
            'action' => $action,
            'reason' => $reason,
            'metadata' => $metadata ?? [
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ],
        ]);
    }

    /**
     * Get audit logs for a specific user
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function forUser(int $userId)
    {
        return static::where('user_id', $userId)->orderBy('created_at', 'desc');
    }

    /**
     * Get audit logs assigned by a specific user
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function assignedBy(int $userId)
    {
        return static::where('assigned_by', $userId)->orderBy('created_at', 'desc');
    }
}
