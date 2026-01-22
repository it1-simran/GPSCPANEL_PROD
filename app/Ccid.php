<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ccid extends Model
{
    protected $fillable = [
        'ccid','esim','customer_name'
    ];
}
