<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BaseModel extends Model
{
    protected function asDateTime($value)
    {
        $datetime = parent::asDateTime($value);

        if (Auth::check() && Auth::user()->timezone) {
            return $datetime->timezone(Auth::user()->timezone);
        }

        return $datetime;
    }
}
