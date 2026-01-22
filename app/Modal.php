<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Modal extends Model
{
    protected $fillable = [
        'name','vendorId','user_id','firmware_id','created_at','updated_at'
    ];
}
