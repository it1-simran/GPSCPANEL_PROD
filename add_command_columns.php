<?php

/**
 * Direct Database Update - Add Command Execution Status Columns
 * Uses direct database connection without Laravel
 */

// Database configuration - update with your credentials
$host = 'localhost';
$db = 'gps_production';  // Correct database name
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "========================================\n";
    echo "Adding Command Execution Columns\n";
    echo "========================================\n\n";

    $table = 'imei_commands';

    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
    if ($stmt->rowCount() === 0) {
        echo "❌ Table '$table' does not exist!\n";
        exit(1);
    }

    echo "✓ Table '$table' found\n\n";

    // SQL to add columns (only if they don't exist)
    $columns = [
        'sent_at' => "ALTER TABLE $table ADD COLUMN sent_at TIMESTAMP NULL DEFAULT NULL COMMENT 'When command was sent' AFTER status",
        'executed_at' => "ALTER TABLE $table ADD COLUMN executed_at TIMESTAMP NULL DEFAULT NULL COMMENT 'When command was executed' AFTER sent_at",
        'device_response' => "ALTER TABLE $table ADD COLUMN device_response LONGTEXT NULL DEFAULT NULL COMMENT 'Response from device' AFTER executed_at",
        'response_time' => "ALTER TABLE $table ADD COLUMN response_time INT NULL DEFAULT NULL COMMENT 'Time in milliseconds' AFTER device_response",
    ];

    foreach ($columns as $colName => $sql) {
        // Check if column exists
        $checkStmt = $pdo->query("SHOW COLUMNS FROM $table LIKE '$colName'");
        
        if ($checkStmt->rowCount() > 0) {
            echo "⊘ Column '$colName' already exists\n";
        } else {
            try {
                $pdo->exec($sql);
                echo "✓ Added column: $colName\n";
            } catch (PDOException $e) {
                echo "⚠ Error adding column '$colName': " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\n========================================\n";
    echo "✓ Migration Completed Successfully!\n";
    echo "========================================\n";

    // Show final table structure
    echo "\nFinal Table Structure:\n";
    echo "─────────────────────────────────────\n";
    
    $stmt = $pdo->query("DESCRIBE $table");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        printf("  • %-20s %s\n", $col['Field'], $col['Type']);
    }
    
    echo "\n";

} catch (PDOException $e) {
    echo "❌ Database Connection Error: " . $e->getMessage() . "\n";
    exit(1);
}
