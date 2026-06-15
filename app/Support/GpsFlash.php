<?php

namespace App\Support;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

class GpsFlash
{
    /**
     * Collect session flash and validation messages for the current request.
     * Uses session()->get() (not pull) so Blade inline alerts and toasts can both read the same data.
     *
     * @return array<string, mixed>
     */
    public static function collect($errors = null): array
    {
        $flash = array_filter([
            'success' => session()->get('success'),
            'error' => session()->get('error'),
            'warning' => session()->get('warning'),
            'info' => session()->get('info'),
            'message' => session()->get('message'),
        ], static function ($value) {
            return $value !== null && $value !== '';
        });

        $status = session()->get('status');
        if (is_string($status) && $status !== '') {
            $flash['status'] = $status;
        }

        $validationErrors = self::collectValidationErrors($errors);
        if ($validationErrors !== []) {
            $flash['validation_errors'] = $validationErrors;
        }

        return $flash;
    }

    /**
     * @return list<string>
     */
    public static function collectValidationErrors($errors): array
    {
        if ($errors instanceof ViewErrorBag) {
            $bag = $errors->getBag('default');
            return $bag->any() ? $bag->all() : [];
        }

        if ($errors instanceof MessageBag && $errors->any()) {
            return $errors->all();
        }

        return [];
    }
}
