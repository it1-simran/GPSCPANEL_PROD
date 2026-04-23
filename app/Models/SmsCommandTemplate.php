<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCommandTemplate extends Model
{
    protected $table = 'sms_command_templates';

    protected $fillable = ['label', 'payload', 'description'];
}
