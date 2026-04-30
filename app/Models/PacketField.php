<?php

namespace App\Models;

class PacketField extends BaseModel
{
    protected $fillable = [
        'packet_type_id', 'name', 'sequence', 'data_type', 
        'length_type', 'length', 'format_rule', 'fixed_value', 
        'min_value', 'max_value', 'regex_pattern',
        'validation_type', 'is_required', 'is_active'
    ];

    public function packetType()
    {
        return $this->belongsTo(PacketType::class);
    }

    public function alertConditions()
    {
        return $this->hasMany(PacketAlertCondition::class);
    }
}
