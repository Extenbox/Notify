<?php

namespace Extenbox\Notify\Facades;

use Illuminate\Support\Facades\Facade;
use Extenbox\Notify\NotifakManager;
use Extenbox\Notify\PendingSms;

/**
 * @method static PendingSms send(string|array $to, string $message)
 * @method static \Extenbox\Notify\Contracts\SmsDriver driver(string $name)
 * @method static NotifakManager configureDriver(string $name, array $config)
 * @method static NotifakManager setDefault(string $name)
 * @method static NotifakManager setFallback(?string $name)
 * @method static bool saveConfigToDatabase(string $name, array $config)
 * @method static NotifakManager extend(string $name, string $driverClass)
 *
 * @see NotifakManager
 */
class Notifak extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'notifak';
    }
}
