<?php

/**
 * Live Tracker Traffic Simulator
 * Run this script with: php simulate_traffic.php
 */

$url = "http://localhost:8000/api/packets/ingest";
$imei = "123456789012345";
$iterations = 50; // Number of packets to send
$delay = 2; // Seconds between packets

echo "Starting Simulation for IMEI: $imei...\n";
echo "Sending $iterations packets with $delay second delay...\n\n";

for ($i = 1; $i <= $iterations; $i++) {
    // Generate random coordinates
    $lat = 28.61 + (rand(-100, 100) / 1000);
    $lon = 77.20 + (rand(-100, 100) / 1000);
    $speed = rand(20, 120);
    
    $payload = [
        "imei" => $imei,
        "data" => "START,LAT:$lat,LON:$lon,SPEED:$speed,TIME:" . date('H:i:s') . ",END"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        echo "[$i/$iterations] Packet Sent: Lat $lat, Lon $lon (Status: $httpCode)\n";
    } else {
        echo "[$i/$iterations] FAILED to send packet. Code: $httpCode. Response: $response\n";
    }

    sleep($delay);
}

echo "\nSimulation Complete!\n";
