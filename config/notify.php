<?php

use Extenbox\Notify\Support\Config;

return [

    /*
    |--------------------------------------------------------------------------
    | پنل پیامکی پیش‌فرض
    |--------------------------------------------------------------------------
    | نام پنل پیامکی که به صورت پیش‌فرض استفاده می‌شود
    | مقادیر مجاز: mediana, melipayamak, ghasedak, smsir, ippanel
    */
    'default' => Config::env('Notify_DEFAULT_DRIVER', 'smsir'),

    /*
    |--------------------------------------------------------------------------
    | پنل پشتیبان (Fallback)
    |--------------------------------------------------------------------------
    | در صورت عدم موفقیت پنل پیش‌فرض، از این پنل استفاده می‌شود
    | null برای غیرفعال کردن
    */
    'fallback' => Config::env('Notify_FALLBACK_DRIVER', null),

    /*
    |--------------------------------------------------------------------------
    | منبع تنظیمات
    |--------------------------------------------------------------------------
    | config : تنظیمات از همین فایل خوانده می‌شود
    | database : تنظیمات از دیتابیس خوانده می‌شود
    */
    'config_source' => Config::env('Notify_CONFIG_SOURCE', 'config'),

    /*
    |--------------------------------------------------------------------------
    | ارسال خودکار
    |--------------------------------------------------------------------------
    | برای حفظ استفاده ساده Notify::send(...) روشن است.
    | در تست‌ها می‌توان آن را خاموش کرد تا ارسال ناخواسته انجام نشود.
    */
    'auto_send' => Config::env('Notify_AUTO_SEND', true),

    /*
    |--------------------------------------------------------------------------
    | SSL Verification
    |--------------------------------------------------------------------------
    | بهتر است در محیط production روشن بماند. برای تست/سرورهایی با certificate
    | مشکل‌دار می‌توانید آن را false کنید.
    */
    'ssl_verify' => Config::env('Notify_SSL_VERIFY', true),

    /*
    |--------------------------------------------------------------------------
    | جدول دیتابیس
    |--------------------------------------------------------------------------
    | نام جدولی که تنظیمات پنل‌ها در آن ذخیره می‌شود (در صورت استفاده از database)
    */
    'table' => 'Notify_providers',

    /*
    |--------------------------------------------------------------------------
    | تنظیمات پنل‌های پیامکی
    |--------------------------------------------------------------------------
    */
    'drivers' => [

        'mediana' => [
            'api_key'     => Config::env('MEDIANA_API_KEY', ''),
            'sender'      => Config::env('MEDIANA_SENDER', ''),
            'base_url'    => 'https://api.mediana.ir',
            'ssl_verify'  => Config::env('MEDIANA_SSL_VERIFY', null),
        ],

        'melipayamak' => [
            'username'    => Config::env('MELIPAYAMAK_USERNAME', ''),
            'password'    => Config::env('MELIPAYAMAK_PASSWORD', ''),
            'sender'      => Config::env('MELIPAYAMAK_SENDER', ''),
            'base_url'    => 'https://rest.payamak-panel.com/api/SendSMS',
            'ssl_verify'  => Config::env('MELIPAYAMAK_SSL_VERIFY', null),
        ],

        'ghasedak' => [
            'api_key'     => Config::env('GHASEDAK_API_KEY', ''),
            'sender'      => Config::env('GHASEDAK_SENDER', ''),
            'base_url'    => 'https://api.ghasedak.me/v2',
            'ssl_verify'  => Config::env('GHASEDAK_SSL_VERIFY', null),
        ],

        'smsir' => [
            'api_key'     => Config::env('SMSIR_API_KEY', ''),
            'sender'      => Config::env('SMSIR_SENDER', ''),
            'base_url'    => 'https://api.sms.ir/v1',
            'ssl_verify'  => Config::env('SMSIR_SSL_VERIFY', null),
        ],

        'ippanel' => [
            'api_key'     => Config::env('IPPANEL_API_KEY', ''),
            'sender'      => Config::env('IPPANEL_SENDER', ''),
            'base_url'    => 'https://edge.ippanel.com/v1',
            'ssl_verify'  => Config::env('IPPANEL_SSL_VERIFY', null),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | لاگ ارسال‌ها
    |--------------------------------------------------------------------------
    */
    'log' => [
        'enabled' => Config::env('Notify_LOG', true),
        'table'   => 'Notify_logs',
    ],

];
