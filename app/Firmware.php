<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Firmware extends Model
{
    protected $fillable = [
        'name','device_category_id','backend_id','configurations','is_deleted','is_default'
    ];

    public function modals() {
        return $this->hasMany(Modal::class, 'firmware_id');
    }
}
