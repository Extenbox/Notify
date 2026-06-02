# 📱 Notify

پکیج PHP پنل پیامک با پشتیبانی از انواع پنل‌ها

[![Latest Version](https://img.shields.io/packagist/v/extenbox/notify.svg)](https://packagist.org/packages/extenbox/notify)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

---

## پنل‌های پشتیبانی‌شده

| پنل | کلید درایور | ارسال معمولی | ارسال Pattern |
|-----|------------|:---:|:---:|
| [مدیانا](https://mediana.ir) | `mediana` | ✅ | ✅ |
| [ملی پیامک](https://melipayamak.com) | `melipayamak` | ✅ | ✅ |
| [قاصدک](https://ghasedak.me) | `ghasedak` | ✅ | ✅ |
| [SMS.ir](https://sms.ir) | `smsir` | ✅ | ✅ |
| [IPPanel](https://ippanel.com) | `ippanel` | ✅ | ✅ |

---

## نصب

```bash
composer require extenbox/notify
```

```bash
# نصب خودکار
php artisan notify:install
```

یا به صورت دستی:

```bash
php artisan vendor:publish --tag=Notify-config
php artisan vendor:publish --tag=Notify-migrations
php artisan migrate
```

---

## تنظیمات

### روش ۱: فایل `.env`

```env
Notify_DEFAULT_DRIVER=smsir
Notify_FALLBACK_DRIVER=ghasedak
Notify_CONFIG_SOURCE=config   # config یا database

# SMS.ir
SMSIR_API_KEY=your-api-key
SMSIR_SENDER=3000xxxxxxxxxxxx

# قاصدک
GHASEDAK_API_KEY=your-api-key
GHASEDAK_SENDER=5000...

# مدیانا
MEDIANA_API_KEY=your-api-key
MEDIANA_SENDER=3000...

# ملی پیامک
MELIPAYAMAK_USERNAME=09xxxxxxxxx
MELIPAYAMAK_PASSWORD=your-password
MELIPAYAMAK_SENDER=5000...

# IPPanel
IPPANEL_API_KEY=your-api-key
IPPANEL_SENDER=983000...
```

### روش ۲: از طریق کد (آرایه)

```php
use Extenbox\Notify\Facades\Notify;

// در AppServiceProvider یا هر کنترلر
Notify::configureDriver('smsir', [
    'api_key' => 'your-api-key',
    'sender'  => '3000xxxx',
]);

Notify::setDefault('smsir');
Notify::setFallback('ghasedak');
```

### روش ۳: دیتابیس

```env
Notify_CONFIG_SOURCE=database
```

```php
// ذخیره تنظیمات در دیتابیس (مثلاً از پنل ادمین)
Notify::saveConfigToDatabase('smsir', [
    'api_key' => 'your-api-key',
    'sender'  => '3000xxxx',
]);

// یا از طریق مدل
use Extenbox\Notify\Models\NotifyProvider;

NotifyProvider::setConfig('ghasedak', [
    'api_key' => 'your-api-key',
    'sender'  => '5000...',
]);
```

---

## نحوه ارسال

### ارسال ساده (تنظیمات پیش‌فرض)

```php
Notify::send('09123456789', 'سلام! خوش آمدید.');

// یا با helper function
notify('09123456789', 'سلام!');
```

### ارسال با تعیین پنل و شماره

```php
Notify::send('09123456789', 'پیام شما')
    ->via('smsir', '3000xxxx');
```

### ارسال با قالب (Pattern)

```php
Notify::send('09123456789', 'کد تأیید: 12345')
    ->via('smsir', '3000...')
    ->type('pattern', 'verify-template', [
        'code' => '12345',
    ]);
```

### ارسال به چند شماره

```php
Notify::send(
    ['09123456789', '09987654321'],
    'پیام گروهی'
)->via('mediana');
```

### دریافت نتیجه

```php
$response = Notify::send('09123456789', 'پیام')->send();

if ($response->isSuccessful()) {
    echo $response->message;     // پیامک با موفقیت ارسال شد
    dump($response->data);       // داده خام پنل
} else {
    echo $response->message;     // متن خطا
    echo $response->statusCode;  // کد وضعیت
}
```

---

## یکپارچه‌سازی با Notification لاراول

```php
// app/Notifications/VerifyPhone.php
use Extenbox\Notify\Channels\NotifyChannel;
use Extenbox\Notify\Channels\NotifyMessage;

class VerifyPhone extends Notification
{
    public function via($notifiable): array
    {
        return [NotifyChannel::class];
    }

    public function toNotify($notifiable): NotifyMessage
    {
        return NotifyMessage::create("کد تأیید: {$this->code}")
            ->via('smsir')
            ->sender('3000...');
    }
}

// استفاده:
$user->notify(new VerifyPhone($code));
```

### با Pattern:

```php
public function toNotify($notifiable): NotifyMessage
{
    return NotifyMessage::create()
        ->via('smsir')
        ->pattern('verify-template', ['code' => $this->code]);
}
```

---

## Trait برای مدل‌ها

```php
// app/Models/User.php
use Extenbox\Notify\Traits\HasNotify;

class User extends Model
{
    use HasNotify;

    public function routeNotificationForNotify(): string
    {
        return $this->mobile;
    }
}

// استفاده:
$user->sendSms('کد تأیید: 12345');
$user->sendSms('پیام شما')->via('ghasedak');
```

---

## کنترلر مدیریت پنل

```php
// routes/web.php
use Extenbox\Notify\Http\Controllers\NotifyController;

Route::prefix('admin/sms')->middleware('auth')->group(function () {
    Route::get('/',               [NotifyController::class, 'index']);
    Route::post('/settings',      [NotifyController::class, 'updateSettings']);
    Route::post('/defaults',      [NotifyController::class, 'updateDefaults']);
    Route::post('/test',          [NotifyController::class, 'testSend']);
    Route::get('/logs',           [NotifyController::class, 'logs']);
    Route::delete('/logs',        [NotifyController::class, 'clearLogs']);
});
```

---

## Artisan Commands

```bash
# نصب پکیج
php artisan Notify:install

# ارسال پیامک آزمایشی
php artisan Notify:test 09123456789
php artisan Notify:test 09123456789 --driver=ghasedak
php artisan Notify:test 09123456789 --driver=smsir --sender=3000... --message="پیام تست"
```

---

## درایور سفارشی

```php
// app/Sms/MyPanelDriver.php
use Extenbox\Notify\Drivers\BaseDriver;
use Extenbox\Notify\Contracts\SmsResponse;

class MyPanelDriver extends BaseDriver
{
    public function getName(): string { return 'mypanel'; }

    public function sendNormal(string|array $to, string $message): SmsResponse
    {
        $response = $this->post('/send', [
            'to'      => $this->normalizePhones($to),
            'message' => $message,
            'from'    => $this->getSender(),
        ]);

        return isset($response['success'])
            ? SmsResponse::success($response)
            : SmsResponse::failure($response['error'] ?? 'خطا');
    }

    public function sendPattern(string|array $to, string $patternCode, array $variables): SmsResponse
    {
        // پیاده‌سازی pattern
        return SmsResponse::success();
    }
}

// ثبت در AppServiceProvider::boot()
Notify::extend('mypanel', MyPanelDriver::class);

// استفاده:
Notify::send('09...', 'پیام')->via('mypanel');
```

---

## مدل لاگ

```php
use Extenbox\Notify\Models\NotifyLog;

// آمار
NotifyLog::stats();
// ['total' => 1234, 'sent' => 1200, 'failed' => 34, 'today' => 56]

// فیلتر
NotifyLog::sent()->today()->get();
NotifyLog::failed()->provider('smsir')->get();
```

---

## ساختار پاسخ `SmsResponse`

| فیلد | نوع | توضیح |
|------|-----|-------|
| `success` | `bool` | موفق بودن ارسال |
| `message` | `string` | پیام وضعیت |
| `data` | `mixed` | داده خام پنل |
| `statusCode` | `int\|null` | کد وضعیت HTTP یا پنل |

```php
$response->isSuccessful()   // bool
$response->toArray()        // array
```

---

## لایسنس

 ساخته شده با ❤️ توسط S
