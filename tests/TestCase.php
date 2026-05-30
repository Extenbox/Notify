<?php

namespace Extenbox\Notify\Tests;

use Extenbox\Notify\NotifakServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [NotifakServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Notifak' => \Notifak\Facades\Notifak::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('notifak.default', 'smsir');
        $app['config']->set('notifak.fallback', null);
        $app['config']->set('notifak.config_source', 'config');
        $app['config']->set('notifak.log.enabled', false);

        $app['config']->set('notifak.drivers.smsir', [
            'api_key'  => 'test-api-key',
            'sender'   => '30007700000000',
            'base_url' => 'https://api.sms.ir/v1',
        ]);
    }
}
