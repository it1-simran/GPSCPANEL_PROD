<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown when an uploaded image is too poor in quality to be processed
 * (blurry, cropped, tilted, low-resolution, or otherwise unreadable).
 *
 * Services must let this exception bubble up UNWRAPPED so the controller can
 * return the exact, user-facing quality message defined in OcrQualityHelper.
 */
class ImageQualityException extends Exception
{
}
