<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class DataFields extends Model
{
    protected $fillable = [
        'fieldName','fieldType','inputType', 'validationConfig','is_active','is_common','is_can_protocol','created_at'
    ];
}

