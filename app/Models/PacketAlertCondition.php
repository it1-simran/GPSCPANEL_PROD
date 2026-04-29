<?php

namespace App\Models;

class PacketAlertCondition extends BaseModel
{
    protected $fillable = ['packet_alert_id', 'packet_field_id', 'operator', 'value'];

    public function alert()
    {
        return $this->belongsTo(PacketAlert::class, 'packet_alert_id');
    }

    public function field()
    {
        return $this->belongsTo(PacketField::class, 'packet_field_id');
    }
}
