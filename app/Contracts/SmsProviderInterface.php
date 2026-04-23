<?php

namespace App\Contracts;

use App\Models\SmsDevice;
use Illuminate\Http\Request;

interface SmsProviderInterface
{
    /**
     * Send an SMS to a device.
     */
    public function send(SmsDevice $device, string $message): string;

    /**
     * Validate the incoming webhook request from the provider.
     */
    public function validateWebhook(Request $request): bool;

    /**
     * Parse the incoming SMS from the webhook.
     * Returns an array with keys: 'from', 'text', 'provider_ref'
     */
    public function parseIncomingSms(Request $request): array;
}
