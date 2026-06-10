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
     * Remove all certificate_details from device configurations.
     * Certificate data should ONLY be stored in the certificate_data field.
     */
    public function up()
    {
        // Get all devices with certificate_details in configurations
        $devices = DB::table('devices')
            ->whereRaw("JSON_EXTRACT(configurations, '$.certificate_details') IS NOT NULL")
            ->get();

        foreach ($devices as $device) {
            $config = json_decode($device->configurations, true) ?: [];

            // Remove certificate_details from configurations
            if (isset($config['certificate_details'])) {
                unset($config['certificate_details']);

                // Update the device with cleaned configurations
                DB::table('devices')
                    ->where('id', $device->id)
                    ->update([
                        'configurations' => json_encode($config)
                    ]);

                echo "✓ Device ID {$device->id}: Removed certificate_details from configurations\n";
            }
        }
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
