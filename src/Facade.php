<?php

declare(strict_types=1);

namespace Extenbox\Notify;

use Extenbox\Notify\Contracts\SmsProviderInterface;

class Facade
{
    protected static ?Notify $notify = null;

    public static function make(?Notify $notify = null): Notify
    {
        if ($notify) {
            static::$notify = $notify;
        }

        return static::$notify ??= new Notify();
    }

    public static function config(array $config): Notify
    {
        return static::make()->config($config);
    }

    public static function provider(string $name): SmsProviderInterface
    {
        return static::make()->provider($name);
    }

    public static function extend(string $name, string|SmsProviderInterface $provider): Notify
    {
        return static::make()->extend($name, $provider);
    }
}
