<?php

/**
 * Live Tracker Traffic Simulator with Command Testing
 * Run this script with: php simulate_traffic.php
 * 
 * This script:
 * 1. Simulates live GPS traffic from device
 * 2. Sends various commands to device
 * 3. Verifies command execution
 * 4. Tests real-world scenarios
 */

// Configuration
$baseUrl = "http://localhost:8000";
$apiIngestUrl = "$baseUrl/api/packets/ingest";
$imei = "490154203237518";
$trafficIterations = 15;
$trafficDelay = 3; // Seconds between traffic packets

// Command scenarios to test
$commands = [
    [
        'name' => 'PING',
        'command' => 'PING',
        'description' => 'Check device connectivity'
    ],
    [
        'name' => 'GET_LOCATION',
        'command' => 'LOC_REQ',
        'description' => 'Request immediate location'
    ],
    [
        'name' => 'GET_SPEED',
        'command' => 'SPD_REQ',
        'description' => 'Request current speed'
    ],
    [
        'name' => 'ENGINE_STOP',
        'command' => 'ENG_STOP',
        'description' => 'Stop engine remotely'
    ],
    [
        'name' => 'ENGINE_START',
        'command' => 'ENG_START',
        'description' => 'Start engine remotely'
    ],
    [
        'name' => 'GEOFENCE_ALERT',
        'command' => 'GEOFENCE:ENABLE',
        'description' => 'Enable geofence monitoring'
    ]
];

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "   LIVE TRACKER TRAFFIC SIMULATOR WITH COMMAND TESTING\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "IMEI: $imei\n";
echo "Base URL: $baseUrl\n";
echo "Traffic Packets: $trafficIterations | Traffic Delay: ${trafficDelay}s\n";
echo "Commands to Test: " . count($commands) . "\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Function to send traffic data
function sendTraffic($imei, $lat, $lon, $speed, $bearing = 0) {
    global $apiIngestUrl;
    
    $payload = [
        "imei" => $imei,
        "latitude" => $lat,
        "longitude" => $lon,
        "speed" => $speed,
        "bearing" => $bearing,
        "altitude" => rand(150, 250),
        "accuracy" => rand(5, 15),
        "timestamp" => date('Y-m-d H:i:s'),
        "data" => "START,LAT:$lat,LON:$lon,SPEED:$speed,TIME:" . date('H:i:s') . ",END"
    ];

    $ch = curl_init($apiIngestUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'success' => ($httpCode >= 200 && $httpCode < 300),
        'code' => $httpCode,
        'response' => $response
    ];
}

// Function to send command
function sendCommand($imei, $command, $commandName) {
    global $baseUrl;
    
    // Try multiple possible API endpoints
    $endpoints = [
        "$baseUrl/api/commands/queue",
        "$baseUrl/api/devices/$imei/command",
        "$baseUrl/api/command/send"
    ];
    
    $result = [
        'sent' => false,
        'endpoint' => null,
        'status' => 'NOT_FOUND',
        'response' => null
    ];
    
    foreach ($endpoints as $endpoint) {
        $payload = [
            "imei" => $imei,
            "command" => $command,
            "command_name" => $commandName,
            "sent_at" => date('Y-m-d H:i:s')
        ];
        
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 400) {
            $result = [
                'sent' => true,
                'endpoint' => $endpoint,
                'status' => $httpCode,
                'response' => $response
            ];
            break;
        }
    }
    
    return $result;
}

// Function to check command status
function checkCommandStatus($imei, $commandName) {
    global $baseUrl;
    
    $endpoint = "$baseUrl/api/commands/status?imei=$imei&command=$commandName";
    
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

// Function to execute queued command (update status to completed)
function executeQueuedCommand($imei, $commandName, $command) {
    global $baseUrl;
    
    $endpoint = "$baseUrl/api/commands/execute";
    
    $payload = [
        "imei" => $imei,
        "command_name" => $commandName,
        "command" => $command,
        "executed_at" => date('Y-m-d H:i:s')
    ];
    
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $startTime = microtime(true);
    $response = curl_exec($ch);
    $responseTime = (int) ((microtime(true) - $startTime) * 1000);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 400) {
        return [
            'success' => true,
            'response' => $response,
            'response_time' => $responseTime
        ];
    } else {
        return [
            'success' => false,
            'message' => "Endpoint not found or execution failed (HTTP $httpCode)",
            'response' => $response
        ];
    }
}

// ====== PHASE 1: SEND TRAFFIC PACKETS ======
echo "[PHASE 1] Simulating Live GPS Traffic\n";
echo "─────────────────────────────────────────────────────────────\n";

$baseLat = 28.61;
$baseLon = 77.20;
$trafficSuccess = 0;
$trafficFailed = 0;

for ($i = 1; $i <= $trafficIterations; $i++) {
    // Simulate movement along a route
    $lat = $baseLat + (($i / $trafficIterations) * 0.05) + (rand(-50, 50) / 10000);
    $lon = $baseLon + (($i / $trafficIterations) * 0.05) + (rand(-50, 50) / 10000);
    $speed = rand(15, 85);
    $bearing = ($i * 10) % 360;
    
    $result = sendTraffic($imei, $lat, $lon, $speed, $bearing);
    
    if ($result['success']) {
        $trafficSuccess++;
        echo "✓ [$i/$trafficIterations] Traffic: LAT $lat | LON $lon | Speed ${speed}km/h (HTTP {$result['code']})\n";
    } else {
        $trafficFailed++;
        echo "✗ [$i/$trafficIterations] FAILED: Traffic packet (HTTP {$result['code']})\n";
    }
    
    sleep($trafficDelay);
}

echo "\nTraffic Summary: ✓ $trafficSuccess sent | ✗ $trafficFailed failed\n\n";

// ====== PHASE 2: SEND COMMANDS ======
echo "[PHASE 2] Sending Device Commands\n";
echo "─────────────────────────────────────────────────────────────\n";

$commandResults = [];
foreach ($commands as $index => $cmd) {
    echo "\n[CMD " . ($index + 1) . "/" . count($commands) . "] {$cmd['name']}\n";
    echo "  Description: {$cmd['description']}\n";
    echo "  Command: {$cmd['command']}\n";
    
    // Send command
    $result = sendCommand($imei, $cmd['command'], $cmd['name']);
    
    if ($result['sent']) {
        echo "  ✓ SENT to: {$result['endpoint']} (HTTP {$result['status']})\n";
        
        // Execute the command immediately
        $executedResult = executeQueuedCommand($imei, $cmd['name'], $cmd['command']);
        if ($executedResult['success']) {
            echo "  ✓ EXECUTED on device (Response Time: {$executedResult['response_time']}ms)\n";
            $commandResults[$cmd['name']] = [
                'status' => 'COMPLETED',
                'httpCode' => $result['status'],
                'response' => $executedResult['response'],
                'response_time' => $executedResult['response_time']
            ];
        } else {
            echo "  ⚠ Execution pending: {$executedResult['message']}\n";
            $commandResults[$cmd['name']] = [
                'status' => 'SENT',
                'httpCode' => $result['status'],
                'response' => $result['response']
            ];
        }
    } else {
        echo "  ✗ FAILED to send command\n";
        $commandResults[$cmd['name']] = [
            'status' => 'FAILED',
            'response' => $result['response']
        ];
    }
    
    sleep(1);
}

// ====== PHASE 3: CHECK COMMAND EXECUTION ======
echo "\n\n[PHASE 3] Checking Command Execution Status\n";
echo "─────────────────────────────────────────────────────────────\n";

$executedCommands = 0;
$failedCommands = 0;

foreach ($commands as $cmd) {
    echo "\nChecking: {$cmd['name']}...\n";
    
    $status = checkCommandStatus($imei, $cmd['name']);
    
    if ($status['code'] == 200 && isset($status['response']['executed'])) {
        if ($status['response']['executed']) {
            echo "  ✓ EXECUTED on device\n";
            echo "  Response Time: " . ($status['response']['response_time'] ?? 'N/A') . "\n";
            echo "  Device Response: " . ($status['response']['device_response'] ?? 'N/A') . "\n";
            $executedCommands++;
        } else {
            echo "  ⏳ PENDING execution...\n";
            echo "  Sent At: " . ($status['response']['sent_at'] ?? 'N/A') . "\n";
            $failedCommands++;
        }
    } else {
        echo "  ⚠ Status Check Failed (HTTP {$status['code']})\n";
        echo "  Note: This may indicate the endpoint doesn't exist or command wasn't found\n";
    }
    
    sleep(1);
}

// ====== FINAL SUMMARY ======
echo "\n\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "                    SIMULATION SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "TRAFFIC PACKETS:\n";
echo "  ✓ Successfully Sent: $trafficSuccess\n";
echo "  ✗ Failed: $trafficFailed\n";
echo "  Success Rate: " . (($trafficSuccess / $trafficIterations) * 100) . "%\n\n";

echo "COMMANDS:\n";
echo "  Total Commands Sent: " . count($commands) . "\n";
echo "  ✓ Executed on Device: $executedCommands\n";
echo "  ✗ Failed/Pending: $failedCommands\n";
echo "  Execution Rate: " . (($executedCommands / count($commands)) * 100) . "%\n\n";

echo "DEVICE INFO:\n";
echo "  IMEI: $imei\n";
echo "  Test Page: $baseUrl/tracker?imei=$imei\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\nSimulation Complete! Check the tracker page for real-time updates.\n\n";
