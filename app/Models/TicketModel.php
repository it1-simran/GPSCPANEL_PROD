<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketModel extends Model
{
    use HasFactory;
    protected $table = 'ticket';
    protected $fillable = [
        'type',
        'subject',
        'file',
        'description',
        'is_read',
        'status',
        'created_by'
    ];
}
