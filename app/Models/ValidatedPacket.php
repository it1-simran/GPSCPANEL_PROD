<?php

namespace App\Models;

class ValidatedPacket extends BaseModel
{
    protected $fillable = ['imei_log_id', 'packet_type_id', 'data', 'is_valid', 'errors'];

    protected $casts = [
        'data' => 'array',
        'errors' => 'array',
        'is_valid' => 'boolean',
    ];

    public function packetType()
    {
        return $this->belongsTo(PacketType::class);
    }

    public function imeiLog()
    {
        return $this->belongsTo(ImeiLog::class, 'imei_log_id');
    }
}
