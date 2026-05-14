<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'protocol_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function steps()
    {
        return $this->hasMany(TestPlanStep::class)->orderBy('sequence');
    }

    public function executions()
    {
        return $this->hasMany(TestPlanExecution::class);
    }
}
