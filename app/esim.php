<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Esim extends Model
{
  protected $fillable = [
    'name',
    'profile_1',
    'profile_2'
  ];

  public function ccids()
  {
    return $this->hasMany(Ccid::class, 'esim', 'id');
  }
}
