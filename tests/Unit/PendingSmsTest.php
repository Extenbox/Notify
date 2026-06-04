<?php

namespace Extenbox\Notify\Tests\Unit;

use Extenbox\Notify\PendingSms;
use Extenbox\Notify\NotifyManager;
use Extenbox\Notify\Tests\TestCase;

class PendingSmsTest extends TestCase
{
    private NotifyManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = $this->app->make('Notify');
    }

    /** @test */
    public function it_stores_phone_and_message(): void
    {
        $pending = new PendingSms($this->manager, '09123456789', 'پیام آزمایشی');

        $this->assertEquals('09123456789', $pending->getTo());
        $this->assertEquals('پیام آزمایشی', $pending->getMessage());
    }

    /** @test */
    public function via_sets_provider_and_sender(): void
    {
        $pending = (new PendingSms($this->manager, '09123456789', 'پیام'))
            ->via('smsir', '30007732000000');

        $this->assertEquals('smsir', $pending->getProvider());
        $this->assertEquals('30007732000000', $pending->getSender());
    }

    /** @test */
    public function it_accepts_array_of_phones(): void
    {
        $phones  = ['09123456789', '09987654321'];
        $pending = new PendingSms($this->manager, $phones, 'پیام گروهی');

        $this->assertIsArray($pending->getTo());
        $this->assertCount(2, $pending->getTo());
    }

    /** @test */
    public function via_without_sender_sets_null_sender(): void
    {
        $pending = (new PendingSms($this->manager, '09123456789', 'پیام'))
            ->via('ghasedak');

        $this->assertEquals('ghasedak', $pending->getProvider());
        $this->assertNull($pending->getSender());
    }

    /** @test */
    public function chaining_works_correctly(): void
    {
        $pending = (new PendingSms($this->manager, '09123456789', '', 'verify-code', ['code' => '1234']))
            ->via('smsir', '30001');

        $this->assertEquals('smsir', $pending->getProvider());
        $this->assertEquals('30001', $pending->getSender());
        $this->assertEquals('verify-code', $pending->getPatternCode());
        $this->assertEquals(['code' => '1234'], $pending->getVariables());
    }
}
