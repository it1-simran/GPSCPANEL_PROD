<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class versionModel extends Model
{
    use HasFactory;
    protected $table = 'version_control';
    protected $fillable = [
        'version',
        'release_notes'
    ];
}
