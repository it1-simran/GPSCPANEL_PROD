<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * A SKU is an approved, reusable device/eSIM/configuration template a
 * customer defines once (see SkuController) and then raises Purchase
 * Orders against (see PurchaseOrderController::store()).
 */
class Sku extends Model
{
    protected $table = 'skus';

    protected $fillable = [
        'sku_code', 'writer_id', 'created_by',
        'device_category_id', 'device_category_name',
        'esim_make', 'esim_profile_1', 'esim_profile_2', 'esim_recharge_period',
        'firmware_id', 'firmware_name', 'model_name', 'vendor_id',
        'configuration',
        'status', 'rejection_reason', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function writer()
    {
        return $this->belongsTo(Writer::class, 'writer_id');
    }

    public function isApproved(): bool
    {
        return $this->status === 'Approved';
    }
}
