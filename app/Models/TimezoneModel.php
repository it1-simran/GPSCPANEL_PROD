<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimezoneModel extends Model
{
    use HasFactory;
    protected $table = 'timezones';
    protected $fillable = [
        'name',
        'utc_offset',
    ];
}
