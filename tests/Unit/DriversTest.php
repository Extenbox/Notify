<?php

namespace Extenbox\Notify\Tests\Unit;

use Extenbox\Notify\Drivers\SmsIr;
use Extenbox\Notify\Tests\TestCase;

class DriversTest extends TestCase
{
    private function makeDriver(array $config = []): SmsIrDriver
    {
        return new SmsIrDriver(array_merge([
            'api_key'  => 'test-key',
            'sender'   => '30007732000000',
            'base_url' => 'https://api.sms.ir/v1',
        ], $config));
    }

    /** @test */
    public function it_returns_correct_driver_name(): void
    {
        $this->assertEquals('smsir', $this->makeDriver()->getName());
    }

    /** @test */
    public function it_returns_sender_from_config(): void
    {
        $driver = $this->makeDriver(['sender' => '30007732000000']);
        $this->assertEquals('30007732000000', $driver->getSender());
    }

    /** @test */
    public function it_can_override_sender(): void
    {
        $driver = $this->makeDriver();
        $driver->setSender('3000999');
        $this->assertEquals('3000999', $driver->getSender());
    }

    /** @test */
    public function set_sender_returns_same_instance(): void
    {
        $driver = $this->makeDriver();
        $result = $driver->setSender('3000999');
        $this->assertSame($driver, $result);
    }

    /** @test */
    public function set_config_merges_and_updates_sender(): void
    {
        $driver = $this->makeDriver();
        $driver->setConfig(['sender' => 'new-sender', 'api_key' => 'new-key']);
        $this->assertEquals('new-sender', $driver->getSender());
    }

    /**
     * تست normalize شماره تلفن (از طریق reflection چون protected است)
     *
     * @test
     * @dataProvider phoneNormalizationProvider
     */
    public function it_normalizes_phone_numbers(string $input, string $expected): void
    {
        $driver     = $this->makeDriver();
        $reflection = new \ReflectionMethod($driver, 'normalizePhone');
        $reflection->setAccessible(true);

        $this->assertEquals($expected, $reflection->invoke($driver, $input));
    }

    public static function phoneNormalizationProvider(): array
    {
        return [
            'شماره با صفر'       => ['09123456789', '989123456789'],
            'شماره بدون صفر'     => ['9123456789',  '989123456789'],
            'شماره با 98'        => ['989123456789', '989123456789'],
            'شماره با خط تیره'  => ['0912-345-6789', '989123456789'],
        ];
    }
}
