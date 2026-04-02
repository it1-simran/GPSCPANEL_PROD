<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImeiCommand extends Model
{
    use HasFactory;

    protected $fillable = [
        'imei_id',
        'command',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'status' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(ImeiDevice::class, 'imei_id');
    }
}
