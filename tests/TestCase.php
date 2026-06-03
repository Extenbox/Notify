<?php

namespace Extenbox\Notify\Tests;

use Extenbox\Notify\NotifyServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [NotifyServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Notify' => \Extenbox\Notify\Facades\Notify::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('Notify.default', 'smsir');
        $app['config']->set('Notify.fallback', null);
        $app['config']->set('Notify.config_source', 'config');
        $app['config']->set('Notify.auto_send', false);
        $app['config']->set('Notify.log.enabled', false);

        $app['config']->set('Notify.drivers.smsir', [
            'api_key'  => 'test-api-key',
            'sender'   => '30007700000000',
            'base_url' => 'https://api.sms.ir/v1',
        ]);
    }
}
