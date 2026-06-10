<?php

namespace App\Services;

use App\Device;

class CertificateImageService
{
    /**
     * Convert a stored (storage/app relative) image into a base64 data URI.
     */
    public function dataUri(?string $relPath): ?string
    {
        if (empty($relPath)) {
            return null;
        }

        $full = storage_path('app/' . ltrim($relPath, '/'));
        if (!is_file($full)) {
            return null;
        }

        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        // Only raster images can be embedded in the certificate (skip PDFs/other types).
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'], true)) {
            return null;
        }

        $data = @file_get_contents($full);
        if ($data === false) {
            return null;
        }

        $mime = match ($ext) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }

    /**
     * Build base64 data URIs for certificate supporting images from certificate_data.ocr_images.
     */
    public function forDevice(Device $device): array
    {
        $ocr = [];
        $certData = [];

        if (!empty($device->certificate_data)) {
            $certData = json_decode($device->certificate_data, true) ?: [];
            $ocr = $certData['ocr_images'] ?? [];
        }

        $rcFrontPath = $ocr['rc_front'] ?? null;
        $rcBackPath = $ocr['rc_back'] ?? null;
        $rcPath = $ocr['rc'] ?? ($certData['file_path'] ?? null);

        if (!$rcFrontPath && $rcPath) {
            $rcFrontPath = $rcPath;
        }

        return [
            'device_image_uri' => $this->dataUri($ocr['device'] ?? null),
            'rc_front_image_uri' => $this->dataUri($rcFrontPath),
            'rc_back_image_uri' => $this->dataUri($rcBackPath),
            'rc_image_uri' => $this->dataUri($rcPath),
            'plate_image_uri' => $this->dataUri($ocr['plate'] ?? null),
        ];
    }
}
