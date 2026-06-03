<?php

namespace Extenbox\Notify\Support;

class Config
{
    public static function load(?string $path = null): array
    {
        $path ??= dirname(__DIR__, 2) . '/config/notify.php';

        if (!is_file($path)) {
            return [];
        }

        $config = require $path;

        return is_array($config) ? $config : [];
    }

    public static function env(string $key, mixed $default = null): mixed
    {
        if (function_exists('env')) {
            return env($key, $default);
        }

        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        return match (strtolower((string) $value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value,
        };
    }
}
