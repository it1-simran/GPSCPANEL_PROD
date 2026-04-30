<?php

namespace App\Models;

class PacketAlert extends BaseModel
{
    protected $fillable = ['packet_type_id', 'name', 'is_active'];

    public function packetType()
    {
        return $this->belongsTo(PacketType::class);
    }

    public function conditions()
    {
        return $this->hasMany(PacketAlertCondition::class);
    }
}
