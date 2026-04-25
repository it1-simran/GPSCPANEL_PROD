<?php

namespace App\Models;

class PacketType extends BaseModel
{
    protected $fillable = ['protocol_id', 'name', 'header_identifier', 'delimiter', 'is_active'];

    public function protocol()
    {
        return $this->belongsTo(Protocol::class);
    }

    public function fields()
    {
        return $this->hasMany(PacketField::class)->orderBy('sequence');
    }
}
