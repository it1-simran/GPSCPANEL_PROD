<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $count = DB::table('jobs')->count();
    echo "Pending jobs in 'jobs' table: $count\n\n";
    if ($count > 0) {
        $jobs = DB::table('jobs')->get();
        foreach ($jobs as $job) {
            echo "ID: " . $job->id . ", Queue: " . $job->queue . ", Attempts: " . $job->attempts . "\n";
            // echo "Payload: " . substr($job->payload, 0, 100) . "...\n";
        }
    }
} catch (\Exception $e) {
    echo "Error checking jobs table: " . $e->getMessage() . "\n";
}
