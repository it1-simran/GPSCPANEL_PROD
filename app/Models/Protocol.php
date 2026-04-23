<?php

namespace App\Models;

class Protocol extends BaseModel
{
    protected $fillable = ['name', 'description', 'is_active'];

    public function packetTypes()
    {
        return $this->hasMany(PacketType::class);
    }
}
