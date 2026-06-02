<?php

namespace App\Services;

/**
 * Centralized OCR image-quality + field-extraction validation.
 *
 * Used consistently across all three OCR modules:
 *   - Upload Registration Certificate (RC)
 *   - Verify Number Plate
 *   - Scan Device Label
 *
 * Two-gate model:
 *   1. Image quality  — is the image clear/readable at all?
 *   2. Field extraction — were all the mandatory fields actually detected?
 */
class OcrQualityHelper
{
    /** Standard, user-facing message shown when an image is not clear/readable. */
    public const QUALITY_ERROR = 'Image quality is not clear. Please upload a clear and readable image.';

    /**
     * Minimum average OCR confidence (0-1) below which the image is treated as
     * blurry / tilted / low-quality. Google Vision typically returns 0.7-0.99
     * for clear text; genuinely poor images fall well below this.
     */
    public const MIN_CONFIDENCE = 0.40;

    /**
     * Minimum number of non-whitespace characters the OCR must return for the
     * image to be considered readable at all. A blank/blurry image yields none.
     */
    public const MIN_CHARS = 4;

    /**
     * Decide whether an OCR result indicates a clear, readable image.
     *
     * @param string     $text       Raw text returned by OCR.
     * @param float|null $confidence Average OCR confidence (0-1), or null if unavailable.
     */
    public static function isReadable(?string $text, ?float $confidence = null): bool
    {
        $clean = preg_replace('/\s+/u', '', (string) $text);

        // Almost no text extracted → blank / blurry / unreadable.
        if (mb_strlen($clean) < self::MIN_CHARS) {
            return false;
        }

        // Confidence is only meaningful when the provider reports a positive value.
        if ($confidence !== null && $confidence > 0 && $confidence < self::MIN_CONFIDENCE) {
            return false;
        }

        return true;
    }

    /**
     * Human-readable label for a field key (used in detailed missing-field errors).
     */
    public static function fieldLabel(string $key): string
    {
        $labels = [
            'vehicle_registration_no' => 'Vehicle Registration No',
            'chassis_no'              => 'Chassis No',
            'engine_no'               => 'Engine No',
            'holder_name'             => 'Owner Name',
            'vehicle_model'           => 'Vehicle Model',
            'color'                   => 'Color',
            'imei'                    => 'IMEI',
            'iccid'                   => 'ICCID',
            'number_plate'            => 'Number Plate',
        ];

        return $labels[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    /**
     * Given a set of required field keys and the extracted data, return the
     * labels of the fields that are missing/empty (in required order).
     *
     * @param string[] $required Required field keys.
     * @param array    $data     Extracted data (key => value).
     * @return string[] Missing field labels.
     */
    public static function missingFieldLabels(array $required, array $data): array
    {
        $missing = [];
        foreach ($required as $key) {
            if (empty($data[$key])) {
                $missing[] = self::fieldLabel($key);
            }
        }
        return $missing;
    }

    /**
     * Build a detailed, user-facing message listing the fields that could not
     * be extracted from the given source.
     *
     * @param string   $source        e.g. "the RC document", "the device label"
     * @param string[] $missingLabels  Field labels that are missing.
     */
    public static function missingFieldsMessage(string $source, array $missingLabels): string
    {
        return 'Could not extract the following required field(s) from ' . $source . ': '
            . implode(', ', $missingLabels)
            . '. Please upload a clear and readable image.';
    }
}
