<?php

namespace App\Support;

use App\Device;

class CertificatePdf
{
    /**
     * Build a download filename that includes IMEI and vehicle registration number.
     */
    public static function filename(Device $device, ?string $vehicleRegistrationNo = null): string
    {
        if ($vehicleRegistrationNo === null || $vehicleRegistrationNo === '') {
            $cert = !empty($device->certificate_data)
                ? json_decode($device->certificate_data, true)
                : [];
            $vehicleRegistrationNo = is_array($cert)
                ? (string) ($cert['vehicle_registration_no'] ?? '')
                : '';
        }

        $parts = ['certificate'];

        $imei = preg_replace('/\D+/', '', (string) ($device->imei ?? ''));
        if ($imei !== '') {
            $parts[] = $imei;
        }

        $regSlug = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', trim((string) $vehicleRegistrationNo)));
        $regSlug = trim($regSlug, '_');
        if ($regSlug !== '') {
            $parts[] = $regSlug;
        }

        return implode('_', $parts) . '.pdf';
    }
}
