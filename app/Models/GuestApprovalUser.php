<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestApprovalUser extends Model
{
    use HasFactory;

    protected $table = 'guestapprovaluser'; // your table name

    protected $fillable = [
        'name',
        'email',
        'phone',
        'userType',
        'deviceCategory',
        'configurations',
        'description',
        'resend_count',
        'status',
    ];
}
?>