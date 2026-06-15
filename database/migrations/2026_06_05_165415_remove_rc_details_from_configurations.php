<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migration.
     *
     * Remove all rc_details and ocr_images from device configurations.
     * OCR-extracted data should ONLY be stored in the certificate_data field.
     */
    public function up()
    {
        // Get all devices with rc_details in configurations
        $devices = DB::table('devices')
            ->whereRaw("JSON_EXTRACT(configurations, '$.rc_details') IS NOT NULL")
            ->orWhereRaw("JSON_EXTRACT(configurations, '$.ocr_images') IS NOT NULL")
            ->get();

        $count = 0;
        foreach ($devices as $device) {
            $config = json_decode($device->configurations, true) ?: [];
            $modified = false;

            // Remove rc_details from configurations
            if (isset($config['rc_details'])) {
                unset($config['rc_details']);
                $modified = true;
            }

            // Remove ocr_images from configurations
            if (isset($config['ocr_images'])) {
                unset($config['ocr_images']);
                $modified = true;
            }

            if ($modified) {
                // Update the device with cleaned configurations
                DB::table('devices')
                    ->where('id', $device->id)
                    ->update([
                        'configurations' => json_encode($config)
                    ]);
                $count++;
                echo "✓ Device ID {$device->id}: Removed OCR data from configurations\n";
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
