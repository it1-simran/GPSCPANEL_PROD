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
$baseUrl = "http://127.0.0.1:8000";
$apiIngestUrl = "$baseUrl/api/packets/ingest";
$imei = isset($argv[1]) ? $argv[1] : "490154203237518";
$trafficIterations = 15;
$trafficDelay = 1; // Seconds between traffic packets

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

// Helper to calculate NMEA checksum
function calculateChecksum($data)
{
    if (str_starts_with($data, '$')) {
        $data = substr($data, 1);
    }
    if (str_contains($data, '*')) {
        $data = explode('*', $data)[0];
    }
    $checksum = 0;
    for ($i = 0; $i < strlen($data); $i++) {
        $checksum ^= ord($data[$i]);
    }
    return str_pad(strtoupper(dechex($checksum)), 2, '0', STR_PAD_LEFT);
}

// Function to send traffic data
function sendTraffic($imei, $lat, $lon, $speed, $bearing = 0, $templateIndex = 0)
{
    global $apiIngestUrl;

    $date = date('dmY');
    $time = date('His');
    $dateTime = date('dmYHis');
    $alt = rand(150, 350) + (rand(0, 999) / 1000);

    // Select Template based on index (alternate between two types)
    $type = $templateIndex % 2;

    if ($type === 0) {
        // Template 1: $NMP (CSV format with XOR)
        $payloadStr = sprintf(
            "\$NMP,JSDE14A,2.2.4,NR,1,L,%s,0,1,%s,%s,%0.6f,N,%0.6f,E,000.0,%0.2f,%d,270.313,0.84,0.50,airtel,1,1,12.6,4.0,0,C,31,404,02,1E84,DC6711F,DC45021,1E84,35,7D3440C,1E84,23,C3FD10F,1E84,20,7D3440D,1E84,20,0010,00,000535,00.0,00.1,0,(0,0,0)",
            $imei,
            $date,
            $time,
            $lat,
            $lon,
            $speed,
            $bearing
        );
    } else {
        // Template 2: $Header (Secure format with XOR and SHA-256)
        $payloadStr = sprintf(
            "\$Header,JSD,2.2.5,EA,10,L,%s,PB01GY0101,1,%s,%s,%0.6f,N,%0.6f,E,%0.1f,%0.1f,%d,268.5,1.38,0.80,airtel,1,1,12.9,0.0,1,C,26,404,02,1E84,7ABF115,DC6711F,1E84,38,7ABF118,1E84,27,C3FD12D,1E84,16,0,0,0,0011,00,000351,%0.3f,%0.3f,%0.3f,()",
            $imei,
            $date,
            $time,
            $lat,
            $lon,
            rand(0, 100) / 10, // Random speed for this template
            rand(0, 360),      // Random bearing
            $bearing,
            rand(0, 1), rand(0, 1), rand(0, 1) // Random extra params
        );
    }

    // Calculate Security Suffixes
    $xor = calculateChecksum($payloadStr);
    
    // Prepare payload for SHA-256 (everything between $ and *)
    $hashPayload = $payloadStr;
    if (str_starts_with($hashPayload, '$')) {
        $hashPayload = substr($hashPayload, 1);
    }
    $sha = hash('sha256', $hashPayload);

    $fullPacket = $payloadStr . '*' . $xor . $sha;

    $payload = [
        "imei" => $imei,
        "latitude" => $lat,
        "longitude" => $lon,
        "speed" => $speed,
        "bearing" => $bearing,
        "altitude" => $alt,
        "accuracy" => rand(5, 15),
        "timestamp" => date('Y-m-d H:i:s'),
        "data" => $fullPacket
    ];

    $ch = curl_init($apiIngestUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fullPacket);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/plain']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);


    return [
        'success' => ($httpCode >= 200 && $httpCode < 300),
        'code' => $httpCode,
        'error' => $error,
        'response' => $response,
        'packet' => $fullPacket
    ];
}

// Function to send command
function sendCommand($imei, $command, $commandName)
{
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

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
function checkCommandStatus($imei, $commandName)
{
    global $baseUrl;


    $endpoint = "$baseUrl/api/commands/status?imei=$imei&command=$commandName";


    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);


    return [
        'code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

// Function to execute queued command (update status to completed)
function executeQueuedCommand($imei, $commandName, $command)
{
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

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

    $result = sendTraffic($imei, $lat, $lon, $speed, $bearing, $i);

    if ($result['success']) {
        $trafficSuccess++;
        $packetHeader = explode(',', $result['packet'])[0];
        echo "✓ [$i/$trafficIterations] $packetHeader: LAT $lat | LON $lon | Speed ${speed}km/h (HTTP {$result['code']})\n";
        echo "   ➜ Response: " . $result['response'] . "\n";
    } else {
        $trafficFailed++;
        $errMsg = $result['error'] ? " | Error: {$result['error']}" : "";
        echo "✗ [$i/$trafficIterations] FAILED: Traffic packet (HTTP {$result['code']}$errMsg)\n";
        echo "   ➜ Error Response: " . $result['response'] . "\n";
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