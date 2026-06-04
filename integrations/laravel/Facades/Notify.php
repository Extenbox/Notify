<?php

namespace Extenbox\Notify\Facades;

use Illuminate\Support\Facades\Facade;
use Extenbox\Notify\NotifyManager;
use Extenbox\Notify\PendingSms;

/**
 * @method static PendingSms message(string|array $to, string $message)
 * @method static PendingSms sms(string|array $to, string $message)
 * @method static \Extenbox\Notify\Contracts\SmsDriver driver(string $name)
 * @method static NotifyManager configureDriver(string $name, array $config)
 * @method static NotifyManager setDefault(string $name)
 * @method static NotifyManager setFallback(?string $name)
 * @method static bool saveConfigToDatabase(string $name, array $config)
 * @method static NotifyManager extend(string $name, string $driverClass)
 *
 * @see NotifyManager
 */
class Notify extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'Notify';
    }
}
