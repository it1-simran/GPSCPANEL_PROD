<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class backend extends Model
{
    protected $fillable = [
        'name','backend_id'
    ];

    public function firmwares() {
        return $this->hasMany(Firmware::class, 'backend_id');
    }
}
