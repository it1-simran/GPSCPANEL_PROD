<?php

namespace App\Services\Sms;

use App\Contracts\SmsProviderInterface;
use App\Models\SmsDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MockSmsProvider implements SmsProviderInterface
{
    public function send(SmsDevice $device, string $message): string
    {
        // Simulate sending an SMS
        return 'MOCK-' . Str::uuid();
    }

    public function validateWebhook(Request $request): bool
    {
        return true;
    }

    public function parseIncomingSms(Request $request): array
    {
        return [
            'from' => $request->input('From'),
            'text' => $request->input('Text'),
            'provider_ref' => $request->input('MessageUUID'),
        ];
    }
}
