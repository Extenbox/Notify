<?php

/**
 * ======================================================
 *  Notify - نمونه‌های کاربردی
 * ======================================================
 */

use Extenbox\Notify\Facades\Notify;

// ─────────────────────────────────────────────
// ۱. ارسال ساده (پنل و شماره پیش‌فرض از config)
// ─────────────────────────────────────────────

Notify::send('09123456789', 'سلام! خوش آمدید.');


// ─────────────────────────────────────────────
// ۲. ارسال با تعیین پنل
// ─────────────────────────────────────────────

Notify::send('09123456789', 'پیام آزمایشی')
    ->via('smsir');


// ─────────────────────────────────────────────
// ۳. ارسال با تعیین پنل و شماره ارسال
// ─────────────────────────────────────────────

Notify::send('09123456789', 'پیام آزمایشی')
    ->via('ghasedak', '5000111122223333');


// ─────────────────────────────────────────────
// ۴. ارسال با قالب (Pattern)
// ─────────────────────────────────────────────

// روش ۱: متغیرها را صریح تعریف کنید
Notify::send('09123456789', 'code: 12345')
    ->via('smsir', '3000...')
    ->type('pattern', 'verify-template', [
        'code' => '12345',
    ]);

// روش ۲: پیام به صورت "key: value" parse می‌شود
Notify::send('09123456789', 'code: 12345, name: علی')
    ->via('smsir')
    ->type('pattern', 'welcome-template');


// ─────────────────────────────────────────────
// ۵. ارسال به چند نفر
// ─────────────────────────────────────────────

Notify::send(
    ['09123456789', '09987654321', '09111111111'],
    'این پیام برای همه ارسال می‌شود'
)->via('mediana');


// ─────────────────────────────────────────────
// ۶. دریافت نتیجه ارسال
// ─────────────────────────────────────────────

$response = Notify::send('09123456789', 'پیام آزمایشی')
    ->via('ippanel')
    ->send(); // فراخوانی send() نتیجه را برمی‌گرداند

if ($response->isSuccessful()) {
    // ارسال موفق
    logger()->info('پیامک ارسال شد', $response->toArray());
} else {
    // خطا
    logger()->error('خطا در ارسال پیامک: ' . $response->message);
}


// ─────────────────────────────────────────────
// ۷. تنظیم درایور از طریق کد (runtime)
// ─────────────────────────────────────────────

Notify::configureDriver('smsir', [
    'api_key' => env('MY_SMSIR_KEY'),
    'sender'  => '30007732000001',
]);

Notify::setDefault('smsir');
Notify::setFallback('ghasedak');


// ─────────────────────────────────────────────
// ۸. ذخیره تنظیمات در دیتابیس (از پنل ادمین)
// ─────────────────────────────────────────────

Notify::saveConfigToDatabase('smsir', [
    'api_key'  => 'your-api-key',
    'sender'   => '30007732000001',
    'base_url' => 'https://api.sms.ir/v1',
]);


// ─────────────────────────────────────────────
// ۹. استفاده از Trait در مدل User
// ─────────────────────────────────────────────

/*
// app/Models/User.php
class User extends Model {
    use \Notify\Traits\HasNotify;

    public function routeNotificationForNotify(): string {
        return $this->mobile;
    }
}

// استفاده:
$user = User::find(1);
$user->sendSms('کد تأیید: 12345');
$user->sendSms('خوش آمدید!')->via('ghasedak');
*/


// ─────────────────────────────────────────────
// ۱۰. درایور سفارشی
// ─────────────────────────────────────────────

/*
// app/Sms/MyCustomDriver.php
class MyCustomDriver extends \Notify\Drivers\BaseDriver {
    public function getName(): string { return 'mycustom'; }

    public function sendNormal(string|array $to, string $message): \Notify\Contracts\SmsResponse {
        // پیاده‌سازی ارسال
        return \Notify\Contracts\SmsResponse::success();
    }

    public function sendPattern(string|array $to, string $patternCode, array $variables): \Notify\Contracts\SmsResponse {
        return \Notify\Contracts\SmsResponse::success();
    }
}

// در AppServiceProvider::boot():
Notify::extend('mycustom', MyCustomDriver::class);

// ارسال:
Notify::send('09...', 'پیام')->via('mycustom');
*/
