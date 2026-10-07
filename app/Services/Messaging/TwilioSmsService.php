<?php

namespace App\Services\Messaging;

use App\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Http;

class TwilioSmsService implements SmsServiceInterface
{
    public function handoff(string $phoneNumber, string $message): array
    {
        return $this->send($phoneNumber, $message);
    }

    public function send(string $phoneNumber, string $message): array
    {
        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $fromNumber = config('services.twilio.from_number');

        if (! $accountSid || ! $authToken || ! $fromNumber) {
            return [
                'handoff_url' => 'sms:' . $this->normalizePhone($phoneNumber) . '?body=' . rawurlencode($message),
                'status' => 'opened',
            ];
        }

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'To' => $this->normalizePhone($phoneNumber),
                'From' => $fromNumber,
                'Body' => $message,
            ]);

        return [
            'handoff_url' => null,
            'status' => $response->successful() ? 'sent' : 'failed',
        ];
    }

    private function normalizePhone(string $phoneNumber): string
    {
        $number = preg_replace('/\D+/', '', $phoneNumber);

        return str_starts_with($number, '0') && strlen($number) === 11
            ? '+' . '91' . substr($number, 1)
            : '+' . $number;
    }
}
