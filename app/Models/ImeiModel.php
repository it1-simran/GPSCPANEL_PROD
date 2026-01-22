<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImeiModel extends Model
{
    use HasFactory;
    protected $table = 'imeis';
    protected $fillable = [
        'imei'
    ];
}
