# Extenbox Notify

Multi provider SMS package for PHP and Laravel.

## Install

```bash
composer require extenbox/notify
```

## Plain PHP usage

```php
use Extenbox\Notify\Notify;

$notify = new Notify();

$notify->config([
    'ippanel' => [
        'api_key' => 'YOUR_IPPANEL_TOKEN',
        'sender' => '+983000505',
    ],
    'melipayamak' => [
        'username' => 'YOUR_USERNAME',
        'password' => 'YOUR_PASSWORD',
        'sender' => '5000...',
    ],
    'ghasedak' => [
        'api_key' => 'YOUR_API_KEY',
        'sender' => '3000...',
    ],
]);

$notify->provider('ippanel')->send('09123456789', 'Hello');
$notify->provider('ippanel')->pattern('09123456789', 'PATTERN_CODE', [
    'code' => '1234',
]);
```

## Static usage

```php
use Extenbox\Notify\Facade as Notify;

Notify::config([
    'ippanel' => [
        'api_key' => 'YOUR_IPPANEL_TOKEN',
        'sender' => '+983000505',
    ],
]);

Notify::provider('ippanel')->send('09123456789', 'Hello');
```

## Laravel controller example

```php
use Extenbox\Notify\Laravel\NotifyFacade as Notify;

class SmsController
{
    public function send()
    {
        Notify::config([
            'ippanel' => [
                'api_key' => request('api_key'),
                'sender' => request('sender'),
            ],
        ]);

        return Notify::provider('ippanel')->send(
            request('phone'),
            request('message')
        );
    }
}
```

If you do not want to use the Laravel facade alias, inject the package service:

```php
use Extenbox\Notify\Notify;

class SmsController
{
    public function send(Notify $notify)
    {
        $notify->config([
            'melipayamak' => [
                'username' => request('username'),
                'password' => request('password'),
                'sender' => request('sender'),
            ],
        ]);

        return $notify->provider('melipayamak')->send(
            request('phone'),
            request('message')
        );
    }
}
```

## Supported providers

- `ippanel` / `mediana`
- `melipayamak`
- `ghasedak`

## Add a custom provider

```php
use Extenbox\Notify\Contracts\SmsProviderInterface;
use Extenbox\Notify\Facade as Notify;

Notify::extend('smsir', SmsIrProvider::class);
```

Your provider must implement `SmsProviderInterface`.
