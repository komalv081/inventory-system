<?php

namespace App\Services\Messaging;

use App\Contracts\SmsServiceInterface;

class DeviceSmsService implements SmsServiceInterface
{
    public function handoff(string $phoneNumber, string $message): array
    {
        $number = preg_replace('/[^+\d]/', '', $phoneNumber);

        return [
            'handoff_url' => 'sms:' . $number . '?body=' . rawurlencode($message),
            'status' => 'opened',
        ];
    }
}