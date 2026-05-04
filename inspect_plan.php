<?php
use Illuminate\Contracts\Console\Kernel;
use App\Models\TestPlan;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$plan = TestPlan::find(1);
foreach ($plan->steps as $step) {
    echo "Step #{$step->sequence} ({$step->step_type}): " . json_encode($step->config) . "\n";
}
