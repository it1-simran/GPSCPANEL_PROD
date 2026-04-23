<?php

namespace Database\Seeders;

use App\Models\Protocol;
use App\Models\PacketType;
use App\Models\PacketField;
use Illuminate\Database\Seeder;

class ProtocolSeeder extends Seeder
{
    public function run()
    {
        // Use updateOrCreate to avoid duplicate entry errors
        $protocol = Protocol::updateOrCreate(
            ['name' => 'BSNL'],
            [
                'description' => 'BSNL Standard Protocol (AIS-140)',
                'is_active' => true
            ]
        );

        $packetType = PacketType::updateOrCreate(
            ['protocol_id' => $protocol->id, 'header_identifier' => '$NMP'],
            [
                'name' => 'Standard NMP Packet',
                'delimiter' => ',',
                'is_active' => true
            ]
        );

        // Define the 52 fields of the $NMP packet matching the provided sample
        $fields = [
            ['name' => 'Header', 'data_type' => 'String', 'validation_type' => 'none'],
            ['name' => 'Vendor ID', 'data_type' => 'String', 'validation_type' => 'none'],
            ['name' => 'Firmware Version', 'data_type' => 'String', 'validation_type' => 'none'],
            ['name' => 'Packet Type', 'data_type' => 'String', 'validation_type' => 'none'],
            ['name' => 'Alert ID', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Mode', 'data_type' => 'String', 'validation_type' => 'none'],
            ['name' => 'IMEI', 'data_type' => 'Numeric', 'validation_type' => 'imei'],
            ['name' => 'Vehicle Registration', 'data_type' => 'String', 'validation_type' => 'none'],
            ['name' => 'GPS Status', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Date', 'data_type' => 'Numeric', 'validation_type' => 'date_ddmmyyyy'],
            ['name' => 'Time', 'data_type' => 'Numeric', 'validation_type' => 'time_hhmmss'],
            ['name' => 'Latitude', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Lat Cardinal', 'data_type' => 'String', 'validation_type' => 'none'],
            ['name' => 'Longitude', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Long Cardinal', 'data_type' => 'String', 'validation_type' => 'none'],
            ['name' => 'Speed', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Course', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Satellites', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Altitude', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'PDOP', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'HDOP', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Operator', 'data_type' => 'String', 'validation_type' => 'none'],
            ['name' => 'Ignition', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Main Power', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Input Voltage', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Internal Battery', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Emergency Status', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Tamper Status', 'data_type' => 'String', 'validation_type' => 'none'],
            ['name' => 'GSM Signal', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'MCC', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'MNC', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'LAC', 'data_type' => 'HEX', 'validation_type' => 'none'],
            ['name' => 'Cell ID', 'data_type' => 'HEX', 'validation_type' => 'none'],
            ['name' => 'NMR 1', 'data_type' => 'HEX', 'validation_type' => 'none'],
            ['name' => 'NMR 2', 'data_type' => 'HEX', 'validation_type' => 'none'],
            ['name' => 'NMR 3', 'data_type' => 'HEX', 'validation_type' => 'none'],
            ['name' => 'Digital Input', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Digital Output', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Analog Input 1', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Analog Input 2', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Analog Input 3', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Analog Input 4', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'External 1', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'External 2', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'External 3', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Internal 1', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Internal 2', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Odometer', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'External Voltage', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Battery Voltage', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Reserved 1', 'data_type' => 'Numeric', 'validation_type' => 'none'],
            ['name' => 'Checksum', 'data_type' => 'String', 'validation_type' => 'nmea_checksum'],
        ];

        // Sync fields
        $packetType->fields()->delete();
        foreach ($fields as $index => $fieldData) {
            $packetType->fields()->create([
                'name' => $fieldData['name'],
                'sequence' => $index + 1,
                'data_type' => $fieldData['data_type'],
                'validation_type' => $fieldData['validation_type'],
                'length_type' => 'Variable',
                'is_active' => true,
                'is_required' => true
            ]);
        }
    }
}
