<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestPlanExecution extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_PASSED = 'passed';
    const STATUS_FAILED = 'failed';
    const STATUS_STOPPED = 'stopped';

    protected $fillable = [
        'test_plan_id',
        'imei_device_id',
        'status',
        'current_step_id',
        'started_at',
        'completed_at',
        'summary',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function testPlan()
    {
        return $this->belongsTo(TestPlan::class);
    }

    public function device()
    {
        return $this->belongsTo(ImeiDevice::class, 'imei_device_id');
    }

    public function currentStep()
    {
        return $this->belongsTo(TestPlanStep::class, 'current_step_id');
    }

    public function logs()
    {
        return $this->hasMany(TestPlanExecutionLog::class, 'execution_id')->orderBy('id');
    }
}
