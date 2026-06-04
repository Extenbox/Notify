<?php

namespace Extenbox\Notify\Tests\Unit;

use Extenbox\Notify\Contracts\SmsResponse;
use Extenbox\Notify\Facades\Notify;
use Extenbox\Notify\NotifyManager;
use Extenbox\Notify\PendingSms;
use Extenbox\Notify\Tests\TestCase;
use Extenbox\Notify\Exceptions\DriverNotFoundException;

class NotifyManagerTest extends TestCase
{
    public function test_send_returns_pending_sms(): void
    {
        $pending = Notify::message('09123456789', 'پیام آزمایشی');
        $this->assertInstanceOf(PendingSms::class, $pending);
    }

    public function test_pending_sms_via_sets_provider(): void
    {
        $pending = Notify::message('09123456789', 'پیام')
            ->via('ghasedak', '5000111122223333');

        $this->assertEquals('ghasedak', $pending->getProvider());
        $this->assertEquals('5000111122223333', $pending->getSender());
    }

    public function test_pending_sms_type_sets_pattern(): void
    {
        $pending = Notify::message('09123456789', 'کد: 12345')
            ->via('smsir')
            ->type('pattern', 'verify-code', ['code' => '12345']);

        $this->assertEquals('pattern', $pending->getType());
        $this->assertEquals('verify-code', $pending->getPatternCode());
        $this->assertEquals(['code' => '12345'], $pending->getVariables());
    }

    public function test_phone_normalization(): void
    {
        $driver    = app('Notify')->driver('smsir');
        $normalize = new \ReflectionMethod($driver, 'normalizePhone');
        $normalize->setAccessible(true);

        $this->assertEquals('989123456789', $normalize->invoke($driver, '09123456789'));
        $this->assertEquals('989123456789', $normalize->invoke($driver, '9123456789'));
        $this->assertEquals('989123456789', $normalize->invoke($driver, '989123456789'));
    }

    public function test_configure_driver_merges_config(): void
    {
        $manager = app('Notify');
        $manager->configureDriver('smsir', ['sender' => '30009999']);

        $driver = $manager->driver('smsir');
        $this->assertEquals('30009999', $driver->getSender());
    }

    public function test_set_default_and_fallback(): void
    {
        $manager = app('Notify');
        $manager->setDefault('ghasedak');
        $manager->setFallback('smsir');

        $this->assertEquals('ghasedak', $manager->getDefaultDriver());
        $this->assertEquals('smsir', $manager->getFallbackDriver());
    }

    public function test_unknown_driver_throws_exception(): void
    {
        $this->expectException(DriverNotFoundException::class);
        app('Notify')->driver('nonexistent');
    }

    public function test_sms_response_success(): void
    {
        $response = SmsResponse::success(['id' => 123], 'ارسال شد');
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('ارسال شد', $response->message);
        $this->assertEquals(['id' => 123], $response->data);
    }

    public function test_sms_response_failure(): void
    {
        $response = SmsResponse::failure('خطا رخ داد', 422);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('خطا رخ داد', $response->message);
        $this->assertEquals(422, $response->statusCode);
    }

    public function test_extend_registers_custom_driver(): void
    {
        $manager = app('Notify');
        $manager->extend('mypanel', \Extenbox\Notify\Drivers\SmsIr::class);

        $this->assertContains('mypanel', $manager->getAvailableDrivers());
    }
}
