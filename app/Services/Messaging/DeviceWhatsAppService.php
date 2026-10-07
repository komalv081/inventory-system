<?php

namespace App\Services\Messaging;

use App\Contracts\WhatsAppServiceInterface;

class DeviceWhatsAppService implements WhatsAppServiceInterface
{
    public function handoff(string $phoneNumber, string $message): string
    {
        $number = preg_replace('/\D+/', '', $phoneNumber);
        $number = str_starts_with($number, '0') && strlen($number) === 11
            ? '91' . substr($number, 1)
            : $number;

        return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
    }
}