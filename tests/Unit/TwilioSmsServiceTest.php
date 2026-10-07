<?php

namespace Tests\Unit;

use App\Services\Messaging\TwilioSmsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TwilioSmsServiceTest extends TestCase
{
    public function test_twilio_sends_normalized_sms_and_returns_sent_status(): void
    {
        config()->set('services.twilio', [
            'account_sid' => 'AC123',
            'auth_token' => 'secret',
            'from_number' => '+15551234567',
        ]);

        Http::fake([
            'https://api.twilio.com/2010-04-01/Accounts/AC123/Messages.json' => Http::response([], 201),
        ])->preventStrayRequests();

        $result = (new TwilioSmsService())->handoff('09632778755', 'Please confirm availability.');

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Basic ' . base64_encode('AC123:secret'))
                && $request->data() === [
                    'To' => '+919632778755',
                    'From' => '+15551234567',
                    'Body' => 'Please confirm availability.',
                ];
        });

        $this->assertSame(['handoff_url' => null, 'status' => 'sent'], $result);
    }
}
