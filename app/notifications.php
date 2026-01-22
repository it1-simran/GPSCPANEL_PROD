<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class notifications extends Model
{
    protected $fillable = [
        'name','user_id','notification','firmware_id','created_at','updated_at','is_view'
    ];
}
