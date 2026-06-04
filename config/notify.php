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
            'base_url'    => 'https://rest.mediana.ir',
        ],

        'melipayamak' => [
            'username'    => Config::env('MELIPAYAMAK_USERNAME', ''),
            'password'    => Config::env('MELIPAYAMAK_PASSWORD', ''),
            'sender'      => Config::env('MELIPAYAMAK_SENDER', ''),
            'base_url'    => 'https://rest.payamak-panel.com/api/SendSMS',
        ],

        'ghasedak' => [
            'api_key'     => Config::env('GHASEDAK_API_KEY', ''),
            'sender'      => Config::env('GHASEDAK_SENDER', ''),
            'base_url'    => 'https://api.ghasedak.me/v2',
        ],

        'smsir' => [
            'api_key'     => Config::env('SMSIR_API_KEY', ''),
            'sender'      => Config::env('SMSIR_SENDER', ''),
            'base_url'    => 'https://api.sms.ir/v1',
        ],

        'ippanel' => [
            'api_key'     => Config::env('IPPANEL_API_KEY', ''),
            'sender'      => Config::env('IPPANEL_SENDER', ''),
            'base_url'    => 'https://edge.ippanel.com/v1public',
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
