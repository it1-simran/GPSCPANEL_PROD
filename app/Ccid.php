<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ccid extends Model
{
    protected $fillable = [
        'ccid','esim','customer_name'
    ];
}
