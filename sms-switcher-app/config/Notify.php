<?php

return [

    /*
    |--------------------------------------------------------------------------
    | پنل پیامکی پیش‌فرض
    |--------------------------------------------------------------------------
    | نام پنل پیامکی که به صورت پیش‌فرض استفاده می‌شود
    | مقادیر مجاز: mediana, melipayamak, ghasedak, smsir, ippanel
    */
    'default' => env('Notify_DEFAULT_DRIVER', 'smsir'),

    /*
    |--------------------------------------------------------------------------
    | پنل پشتیبان (Fallback)
    |--------------------------------------------------------------------------
    | در صورت عدم موفقیت پنل پیش‌فرض، از این پنل استفاده می‌شود
    | null برای غیرفعال کردن
    */
    'fallback' => env('Notify_FALLBACK_DRIVER', null),

    /*
    |--------------------------------------------------------------------------
    | منبع تنظیمات
    |--------------------------------------------------------------------------
    | config : تنظیمات از همین فایل خوانده می‌شود
    | database : تنظیمات از دیتابیس خوانده می‌شود
    */
    'config_source' => env('Notify_CONFIG_SOURCE', 'config'),

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
            'api_key'     => env('MEDIANA_API_KEY', ''),
            'sender'      => env('MEDIANA_SENDER', ''),
            'base_url'    => 'https://rest.mediana.ir',
        ],

        'melipayamak' => [
            'username'    => env('MELIPAYAMAK_USERNAME', ''),
            'password'    => env('MELIPAYAMAK_PASSWORD', ''),
            'sender'      => env('MELIPAYAMAK_SENDER', ''),
            'base_url'    => 'https://rest.payamak-panel.com/api/SendSMS',
        ],

        'ghasedak' => [
            'api_key'     => env('GHASEDAK_API_KEY', ''),
            'sender'      => env('GHASEDAK_SENDER', ''),
            'base_url'    => 'https://api.ghasedak.me/v2',
        ],

        'smsir' => [
            'api_key'     => env('SMSIR_API_KEY', ''),
            'sender'      => env('SMSIR_SENDER', ''),
            'base_url'    => 'https://api.sms.ir/v1',
        ],

        'ippanel' => [
            'api_key'     => env('IPPANEL_API_KEY', ''),
            'sender'      => env('IPPANEL_SENDER', ''),
            'base_url'    => 'https://edge.ippanel.com/v1public',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | لاگ ارسال‌ها
    |--------------------------------------------------------------------------
    */
    'log' => [
        'enabled' => env('Notify_LOG', true),
        'table'   => 'Notify_logs',
    ],

];
