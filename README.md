# Notify

Framework-agnostic PHP SMS package with optional Laravel integration.

Supported providers: `mediana`, `melipayamak`, `ghasedak`, `smsir`, `ippanel`.

## Install

```bash
composer require extenbox/notify
```

The package core only requires PHP 8.1+ and Guzzle. Laravel components are optional and are used only inside Laravel projects.

## Package Layout

- `src/` contains the framework-independent SMS core.
- `db/migrations/` contains raw migration files that each framework can copy or publish where it needs.
- `integrations/laravel/` contains Laravel helpers such as service provider, facade, artisan commands, notification channel, Eloquent models, and controller.

## Plain PHP

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Extenbox\Notify\Notify;

Notify::configure([
    'default' => 'smsir',
    'fallback' => null,
    'auto_send' => false,
    'ssl_verify' => true,
    'log' => ['enabled' => false],
    'drivers' => [
        'smsir' => [
            'api_key' => 'your-api-key',
            'sender' => '3000xxxx',
            'base_url' => 'https://api.sms.ir/v1',
            'ssl_verify' => true,
        ],
    ],
]);

$response = Notify::message('09123456789', 'سلام')->send();

if ($response->isSuccessful()) {
    echo $response->message;
}
```

## CodeIgniter

You can create a small service and reuse the same core manager.

```php
<?php

namespace App\Services;

use Extenbox\Notify\NotifyManager;

class SmsService
{
    public function notify(): NotifyManager
    {
        return new NotifyManager([
            'default' => 'smsir',
            'auto_send' => false,
            'ssl_verify' => true,
            'log' => ['enabled' => false],
            'drivers' => [
                'smsir' => [
                    'api_key' => getenv('SMSIR_API_KEY'),
                    'sender' => getenv('SMSIR_SENDER'),
                    'base_url' => 'https://api.sms.ir/v1',
                    'ssl_verify' => true,
                ],
            ],
        ]);
    }
}
```

Usage:

```php
$response = service('sms')->notify()
    ->message('09123456789', 'کد تایید: 1234')
    ->send();
```

## Laravel

Laravel auto-discovery still works. The old facade and helper style remain available.

```bash
php artisan notify:install
php artisan vendor:publish --tag=Notify-config
php artisan vendor:publish --tag=Notify-migrations
php artisan migrate
```

The Laravel service provider publishes migrations from `db/migrations/` into your app's `database/migrations` directory.

```php
use Extenbox\Notify\Facades\Notify;

Notify::message('09123456789', 'سلام! خوش آمدید.')->send();

$response = Notify::message('09123456789', 'پیام')->send();
```

## Pattern SMS

```php
use Extenbox\Notify\Notify;

Notify::message('09123456789', 'code: 12345')
    ->via('smsir', '3000xxxx')
    ->type('pattern', 'verify-template', [
        'code' => '12345',
    ])
    ->send();
```

Use `Notify::message($to, $message)` to start a message chain, then call final `send()`.

## Runtime Configuration

```php
use Extenbox\Notify\Notify;

Notify::configureDriver('smsir', [
    'api_key' => 'new-key',
    'sender' => '3000xxxx',
    'ssl_verify' => true,
]);

Notify::setDefault('smsir');
Notify::setFallback('ghasedak');
```

## Driver SDK Methods

You can access provider-specific SDK methods through the manager:

```php
$smsir = Notify::driver('smsir');
$credit = $smsir->getCredit();
$sent = $smsir->getSentReport(['page' => 1, 'pageSize' => 10]);

$ippanel = Notify::driver('ippanel');
$balance = $ippanel->getCredit();
$message = $ippanel->getMessage(123456);
$inbox = $ippanel->fetchInbox(1, 10);

$ghasedak = Notify::driver('ghasedak');
$account = $ghasedak->accountInfo();
$status = $ghasedak->status(['message-id-1', 'message-id-2']);

$mediana = Notify::driver('mediana');
$balance = $mediana->getBalance();
$lines = $mediana->getLines();

$meliPayamak = Notify::driver('melipayamak');
$credit = $meliPayamak->getCredit();
$messages = $meliPayamak->getMessages(1, 0, 10);
```

Every driver also supports raw requests for newly added provider endpoints:

```php
$response = Notify::driver('smsir')->rawGet('report/sent', ['page' => 1]);
$response = Notify::driver('ghasedak')->rawPostForm('sms/status', ['id' => '123', 'type' => 1]);
$response = Notify::driver('ippanel')->rawPost('api/report/messages', ['page' => 1]);
```

## Notes

- `Extenbox\Notify\Notify` is the framework-independent static gateway.
- `Extenbox\Notify\NotifyManager` is the framework-independent manager class.
- `integrations/laravel/` contains Laravel-only adapters while preserving their previous namespaces for compatibility.
- `ssl_verify` controls Guzzle SSL certificate verification. Keep it `true` in production; set it to `false` only for local/testing environments with certificate issues. The alias `sslverify` is also accepted.
- Driver `base_url` values may include a path such as `/v1`; request endpoints are joined safely, so `https://api.sms.ir/v1` plus `send/bulk` stays under `/v1/send/bulk`.
- In non-Laravel projects, set `'log' => ['enabled' => false]` unless you implement logging yourself.
