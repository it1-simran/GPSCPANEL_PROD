<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migration.
     *
     * Remove all ocr_images from device configurations.
     * OCR image paths should ONLY be stored in the certificate_data field.
     */
    public function up()
    {
        // Get all devices with ocr_images in configurations
        $devices = DB::table('devices')
            ->whereRaw("JSON_EXTRACT(configurations, '$.ocr_images') IS NOT NULL")
            ->get();

        $count = 0;
        foreach ($devices as $device) {
            $config = json_decode($device->configurations, true) ?: [];

            // Remove ocr_images from configurations
            if (isset($config['ocr_images'])) {
                unset($config['ocr_images']);

                // Update the device with cleaned configurations
                DB::table('devices')
                    ->where('id', $device->id)
                    ->update([
                        'configurations' => json_encode($config)
                    ]);
                $count++;
                echo "✓ Device ID {$device->id}: Removed ocr_images from configurations\n";
            }
        }

        echo "\n✓ Total devices cleaned: $count\n";
    }

    /**
     * Reverse the migration.
     */
    public function down()
    {
        // This migration cannot be safely reversed since we're removing data
        // If needed, restore from backup
    }
};
