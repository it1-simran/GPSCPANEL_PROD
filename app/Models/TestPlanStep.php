<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestPlanStep extends Model
{
    use HasFactory;

    const TYPE_SEND_COMMAND = 'send_command';
    const TYPE_WAIT_FOR_RESPONSE = 'wait_for_response';
    const TYPE_VALIDATE_RESPONSE = 'validate_response';
    const TYPE_CONDITIONAL_FLOW = 'conditional_flow';
    const TYPE_ALERT_EVALUATION = 'alert_evaluation';

    protected $fillable = [
        'test_plan_id',
        'sequence',
        'step_type',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function testPlan()
    {
        return $this->belongsTo(TestPlan::class);
    }
}
