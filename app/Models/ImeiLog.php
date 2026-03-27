<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImeiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'imei_id',
        'raw_packet',
        'source_ip',
        'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(ImeiDevice::class, 'imei_id');
    }
}
