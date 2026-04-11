<?php

$migrationsPath = 'database/migrations';
$filesToCheck = [
    '2026_01_27_082428_add_is_certification_to_device_categories_table.php',
    '2026_02_03_000001_add_certification_fields_to_device_categories_table.php',
    '2026_02_27_065853_add_timezone_to_guestapprovaluser_table.php',
    '2026_04_02_000001_alter_imei_devices_for_recording_windows.php',
    '2026_04_02_000002_add_indexes_to_imei_logs.php',
    '2026_04_02_000003_alter_imei_commands_for_delivery_tracking.php'
];

foreach ($filesToCheck as $file) {
    $filePath = $migrationsPath . '/' . $file;
    if (!file_exists($filePath)) continue;
    
    $content = file_get_contents($filePath);
    
    // Pattern to find Schema::table('tableName', function (Blueprint $table) {
    // and identify the table name.
    
    if (preg_match('/Schema::table\(\s*\'([^\']+)\'/', $content, $matches)) {
        $tableName = $matches[1];
        
        // Find all $table->something('columnName')... 
        // We want to wrap them in if (!Schema::hasColumn($tableName, $columnName)) { ... }
        
        $newContent = preg_replace_callback(
            '/\$table->(?!dropColumn|dropIndex|index|unique)([a-zA-Z]+)\(\s*\'([^\']+)\'(.*?)\);/s',
            function ($innerMatches) use ($tableName) {
                $type = $innerMatches[1];
                $column = $innerMatches[2];
                $rest = $innerMatches[3];
                return "if (!Schema::hasColumn('$tableName', '$column')) {\n                \$table->$type('$column'$rest);\n            }";
            },
            $content
        );
        
        if ($newContent !== $content) {
            file_put_contents($filePath, $newContent);
            echo "Added defensive column checks to $file\n";
        }
    }
}

// Special case for update_configurations_column_in_firmware_table.php 
// Since it's a raw DB statement, I'll just skip it for now or wrap it manually.
echo "Finished column-level fixes.\n";
