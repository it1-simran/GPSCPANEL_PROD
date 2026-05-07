<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

use App\Models\PacketType;

$pt = PacketType::find(25);
if (!$pt) {
    echo "PacketType 25 not found\n";
} else {
    echo "PacketType 25: " . $pt->name . "\n";
    echo "Protocol ID: " . $pt->protocol_id . "\n";
    echo "Protocol: " . ($pt->protocol ? $pt->protocol->name : 'NULL') . "\n";
    
    $alerts = $pt->alerts()->with('conditions.field')->get();
    echo "Alerts count: " . $alerts->count() . "\n";
    foreach ($alerts as $alert) {
        echo " - Alert: " . $alert->name . "\n";
        foreach ($alert->conditions as $cond) {
            echo "   - Condition: " . ($cond->field ? $cond->field->name : 'NULL FIELD') . " " . $cond->operator . " " . $cond->value . "\n";
        }
    }
}
