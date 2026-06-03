<?php

namespace Extenbox\Notify\Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Extenbox\Notify\Contracts\SmsResponse;
use Extenbox\Notify\Drivers\SmsIr;
use Extenbox\Notify\Tests\TestCase;

class SmsDriversTest extends TestCase
{
    private function mockSmsIrDriver(array $responses): SmsIr
    {
        $mock    = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);

        $driver = new SmsIr([
            'api_key'  => 'test-key',
            'sender'   => '30007732000000',
            'base_url' => 'https://api.sms.ir/v1',
        ]);

        // inject mock client
        $reflection = new \ReflectionProperty($driver, 'client');
        $reflection->setAccessible(true);
        $reflection->setValue($driver, $client);

        return $driver;
    }

    /** @test */
    public function smsir_send_normal_returns_success_on_valid_response(): void
    {
        $driver = $this->mockSmsIrDriver([
            new Response(200, [], json_encode([
                'status'  => 1,
                'message' => 'success',
                'data'    => ['messageId' => 12345],
            ])),
        ]);

        $response = $driver->sendNormal('09123456789', 'پیام آزمایشی');

        $this->assertInstanceOf(SmsResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }

    /** @test */
    public function smsir_send_normal_returns_failure_on_error_response(): void
    {
        $driver = $this->mockSmsIrDriver([
            new Response(200, [], json_encode([
                'status'  => 0,
                'message' => 'Invalid API Key',
            ])),
        ]);

        $response = $driver->sendNormal('09123456789', 'پیام');

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('Invalid API Key', $response->message);
    }

    /** @test */
    public function smsir_send_pattern_returns_success(): void
    {
        $driver = $this->mockSmsIrDriver([
            new Response(200, [], json_encode([
                'status' => 1,
                'data'   => ['messageId' => 99],
            ])),
        ]);

        $response = $driver->sendPattern('09123456789', '12345', ['code' => '7890']);

        $this->assertTrue($response->isSuccessful());
    }

    /** @test */
    public function sms_response_to_array_contains_all_keys(): void
    {
        $response = SmsResponse::success(['id' => 1], 'ارسال شد');

        $array = $response->toArray();

        $this->assertArrayHasKey('success', $array);
        $this->assertArrayHasKey('message', $array);
        $this->assertArrayHasKey('data', $array);
        $this->assertArrayHasKey('status_code', $array);
        $this->assertTrue($array['success']);
    }

    /** @test */
    public function failure_response_is_not_successful(): void
    {
        $response = SmsResponse::failure('خطای سرور', 500);

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('خطای سرور', $response->message);
        $this->assertEquals(500, $response->statusCode);
    }
}
