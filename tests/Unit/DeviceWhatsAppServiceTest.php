<?php

namespace Tests\Unit;

use App\Services\Messaging\DeviceWhatsAppService;
use PHPUnit\Framework\TestCase;

class DeviceWhatsAppServiceTest extends TestCase
{
    public function test_indian_mobile_number_with_leading_zero_is_normalized(): void
    {
        $service = new DeviceWhatsAppService();

        $url = $service->handoff('09632778755', 'Please confirm availability.');

        $this->assertSame(
            'https://wa.me/919632778755?text=Please%20confirm%20availability.',
            $url
        );
    }
}
