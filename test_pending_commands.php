<?php
/**
 * Test script for pending commands endpoint
 * 
 * This script tests the new /api/pending-commands endpoint
 */

// Configuration
$baseUrl = "http://localhost:8000";
$imei = "490154203237518";
$token = "your_device_token_here"; // Replace with actual device token

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "         TESTING PENDING COMMANDS ENDPOINT\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "IMEI: $imei\n";
echo "Base URL: $baseUrl\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Step 1: First, let's check if we need to insert test commands
echo "[STEP 1] Checking/Creating test data\n";
echo "─────────────────────────────────────────────────────────────\n";

// Note: In a real test, you would:
// 1. Ensure imei_devices has a record for the IMEI
// 2. Ensure imei_commands has pending (status=0) records for that IMEI

// For now, we'll just show what data structure we need
$testData = [
    'imei_devices' => [
        'id' => 1,
        'imei' => $imei,
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s')
    ],
    'imei_commands' => [
        [
            'id' => 1,
            'imei_id' => 1,
            'command' => 'PING',
            'status' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 2,
            'imei_id' => 1,
            'command' => 'ENG_STOP',
            'status' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]
];

echo "Required test data structure:\n";
echo json_encode($testData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

// Step 2: Test the pending commands endpoint
echo "[STEP 2] Testing POST /api/pending-commands\n";
echo "─────────────────────────────────────────────────────────────\n";

$endpoint = $baseUrl . "/api/pending-commands";
$payload = [
    "imei" => $imei
];

echo "Endpoint: $endpoint\n";
echo "Request Payload:\n";
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

echo "Sending request...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ CURL Error: $error\n";
} else {
    echo "HTTP Code: $httpCode\n\n";
    echo "Response:\n";
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    } else {
        echo $response . "\n";
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "                    TEST COMPLETE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Step 3: Instructions for setting up test data
echo "Setup Instructions:\n";
echo "─────────────────────────────────────────────────────────────\n";
echo "1. Create imei_devices record:\n";
echo "   INSERT INTO imei_devices (imei, status) VALUES ('$imei', 'active');\n\n";
echo "2. Create pending commands:\n";
echo "   INSERT INTO imei_commands (imei_id, command, status) \n";
echo "   VALUES (1, 'PING', 0), (1, 'ENG_STOP', 0);\n\n";
echo "3. Get a valid device token (from devices.api_token)\n\n";
echo "4. Update the test script with the correct token\n\n";
echo "5. Run: php test_pending_commands.php\n";
