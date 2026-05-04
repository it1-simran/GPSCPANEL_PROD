<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestPlanExecutionLog extends Model
{
    use HasFactory;

    const STATUS_PASS = 'pass';
    const STATUS_FAIL = 'fail';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_INFO = 'info';

    protected $fillable = [
        'execution_id',
        'step_id',
        'status',
        'input_data',
        'output_data',
        'error_message',
        'duration_ms',
    ];

    protected $casts = [
        'input_data' => 'array',
        'output_data' => 'array',
    ];

    public function execution()
    {
        return $this->belongsTo(TestPlanExecution::class, 'execution_id');
    }

    public function step()
    {
        return $this->belongsTo(TestPlanStep::class, 'step_id');
    }
}
