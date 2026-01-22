<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'template_name','id_user','device_category_id','configurations','can_configurations','verify','default_template'
    ];
}
