# 📱 Notifak

پکیج پنل پیامک برای لاراول با پشتیبانی از پنل‌های ایرانی

[![Latest Version](https://img.shields.io/packagist/v/extenbox/notify.svg)](https://packagist.org/packages/extenbox/notify)
[![Laravel](https://img.shields.io/badge/Laravel-10%20|%2011-red.svg)](https://laravel.com)
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
php artisan notifak:install
```

یا به صورت دستی:

```bash
php artisan vendor:publish --tag=notifak-config
php artisan vendor:publish --tag=notifak-migrations
php artisan migrate
```

---

## تنظیمات

### روش ۱: فایل `.env`

```env
NOTIFAK_DEFAULT_DRIVER=smsir
NOTIFAK_FALLBACK_DRIVER=ghasedak
NOTIFAK_CONFIG_SOURCE=config   # config یا database

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
use Extenbox\Notify\Facades\Notifak;

// در AppServiceProvider یا هر کنترلر
Notifak::configureDriver('smsir', [
    'api_key' => 'your-api-key',
    'sender'  => '3000xxxx',
]);

Notifak::setDefault('smsir');
Notifak::setFallback('ghasedak');
```

### روش ۳: دیتابیس

```env
NOTIFAK_CONFIG_SOURCE=database
```

```php
// ذخیره تنظیمات در دیتابیس (مثلاً از پنل ادمین)
Notifak::saveConfigToDatabase('smsir', [
    'api_key' => 'your-api-key',
    'sender'  => '3000xxxx',
]);

// یا از طریق مدل
use Extenbox\Notify\Models\NotifakProvider;

NotifakProvider::setConfig('ghasedak', [
    'api_key' => 'your-api-key',
    'sender'  => '5000...',
]);
```

---

## نحوه ارسال

### ارسال ساده (تنظیمات پیش‌فرض)

```php
Notifak::send('09123456789', 'سلام! خوش آمدید.');

// یا با helper function
notifak('09123456789', 'سلام!');
```

### ارسال با تعیین پنل و شماره

```php
Notifak::send('09123456789', 'پیام شما')
    ->via('smsir', '3000xxxx');
```

### ارسال با قالب (Pattern)

```php
Notifak::send('09123456789', 'کد تأیید: 12345')
    ->via('smsir', '3000...')
    ->type('pattern', 'verify-template', [
        'code' => '12345',
    ]);
```

### ارسال به چند شماره

```php
Notifak::send(
    ['09123456789', '09987654321'],
    'پیام گروهی'
)->via('mediana');
```

### دریافت نتیجه

```php
$response = Notifak::send('09123456789', 'پیام')->send();

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
use Extenbox\Notify\Channels\NotifakChannel;
use Extenbox\Notify\Channels\NotifakMessage;

class VerifyPhone extends Notification
{
    public function via($notifiable): array
    {
        return [NotifakChannel::class];
    }

    public function toNotifak($notifiable): NotifakMessage
    {
        return NotifakMessage::create("کد تأیید: {$this->code}")
            ->via('smsir')
            ->sender('3000...');
    }
}

// استفاده:
$user->notify(new VerifyPhone($code));
```

### با Pattern:

```php
public function toNotifak($notifiable): NotifakMessage
{
    return NotifakMessage::create()
        ->via('smsir')
        ->pattern('verify-template', ['code' => $this->code]);
}
```

---

## Trait برای مدل‌ها

```php
// app/Models/User.php
use Extenbox\Notify\Traits\HasNotifak;

class User extends Model
{
    use HasNotifak;

    public function routeNotificationForNotifak(): string
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
use Extenbox\Notify\Http\Controllers\NotifakController;

Route::prefix('admin/sms')->middleware('auth')->group(function () {
    Route::get('/',               [NotifakController::class, 'index']);
    Route::post('/settings',      [NotifakController::class, 'updateSettings']);
    Route::post('/defaults',      [NotifakController::class, 'updateDefaults']);
    Route::post('/test',          [NotifakController::class, 'testSend']);
    Route::get('/logs',           [NotifakController::class, 'logs']);
    Route::delete('/logs',        [NotifakController::class, 'clearLogs']);
});
```

---

## Artisan Commands

```bash
# نصب پکیج
php artisan notifak:install

# ارسال پیامک آزمایشی
php artisan notifak:test 09123456789
php artisan notifak:test 09123456789 --driver=ghasedak
php artisan notifak:test 09123456789 --driver=smsir --sender=3000... --message="پیام تست"
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
Notifak::extend('mypanel', MyPanelDriver::class);

// استفاده:
Notifak::send('09...', 'پیام')->via('mypanel');
```

---

## مدل لاگ

```php
use Extenbox\Notify\Models\NotifakLog;

// آمار
NotifakLog::stats();
// ['total' => 1234, 'sent' => 1200, 'failed' => 34, 'today' => 56]

// فیلتر
NotifakLog::sent()->today()->get();
NotifakLog::failed()->provider('smsir')->get();
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

MIT — ساخته شده با ❤️ برای جامعه Laravel ایران
