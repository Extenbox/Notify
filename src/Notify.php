<?php

namespace Extenbox\Notify;

use Extenbox\Notify\Contracts\SmsDriver;
use Extenbox\Notify\Contracts\SmsResponse;
use Extenbox\Notify\Support\Config;

class Notify
{
    protected static ?NotifyManager $manager = null;

    public static function manager(?array $config = null): NotifyManager
    {
        if ($config !== null || static::$manager === null) {
            static::$manager = new NotifyManager($config ?? Config::load());
        }

        return static::$manager;
    }

    public static function configure(array $config): NotifyManager
    {
        return static::manager($config);
    }

    public static function send(string|array $to, string $message): PendingSms
    {
        return static::manager()->send($to, $message);
    }

    public static function message(string|array $to, string $message): PendingSms
    {
        return static::manager()->message($to, $message);
    }

    public static function info(string|array $to, string $message): PendingSms
    {
        return static::manager()->info($to, $message);
    }

    public static function sms(string|array $to, string $message): PendingSms
    {
        return static::manager()->sms($to, $message);
    }

    public static function driver(string $name): SmsDriver
    {
        return static::manager()->driver($name);
    }

    public static function dispatch(PendingSms $pending): SmsResponse
    {
        return static::manager()->dispatch($pending);
    }

    public static function __callStatic(string $method, array $arguments): mixed
    {
        return static::manager()->{$method}(...$arguments);
    }
}
