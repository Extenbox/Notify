<?php

/**
 * =====================================================
 * نمونه کنترلر مدیریت تنظیمات پنل پیامک
 * این فایل یک مثال است و باید در پروژه خودتان استفاده شود
 * =====================================================
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Extenbox\Notify\Facades\Notify;

class SmsProviderController extends Controller
{
    /**
     * نمایش فرم تنظیمات
     */
    public function index(): View
    {
        $providers = [
            'smsir'       => ['label' => 'SMS.ir',      'fields' => ['api_key', 'sender']],
            'ghasedak'    => ['label' => 'قاصدک',       'fields' => ['api_key', 'sender']],
            'mediana'     => ['label' => 'مدیانا',      'fields' => ['api_key', 'sender']],
            'melipayamak' => ['label' => 'ملی پیامک',   'fields' => ['username', 'password', 'sender']],
            'ippanel'     => ['label' => 'IPPanel',     'fields' => ['api_key', 'sender']],
        ];

        return view('admin.sms.settings', [
            'providers'      => $providers,
            'currentDefault' => config('Notify.default'),
            'currentFallback'=> config('Notify.fallback'),
        ]);
    }

    /**
     * ذخیره تنظیمات یک پنل در دیتابیس
     */
    public function updateProvider(Request $request, string $driver): RedirectResponse
    {
        $request->validate([
            'sender'   => 'required|string|max:20',
            'api_key'  => 'nullable|string',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
        ]);

        $config = array_filter([
            'api_key'  => $request->api_key,
            'username' => $request->username,
            'password' => $request->password,
            'sender'   => $request->sender,
        ]);

        $saved = Notify::saveConfigToDatabase($driver, $config);

        if ($saved) {
            return back()->with('success', "تنظیمات {$driver} با موفقیت ذخیره شد.");
        }

        return back()->with('error', 'خطا در ذخیره تنظیمات.');
    }

    /**
     * تنظیم پنل پیش‌فرض و پشتیبان
     */
    public function setDefaults(Request $request): RedirectResponse
    {
        $request->validate([
            'default'  => 'required|in:smsir,ghasedak,mediana,melipayamak,ippanel',
            'fallback' => 'nullable|in:smsir,ghasedak,mediana,melipayamak,ippanel',
        ]);

        // ذخیره در دیتابیس یا config
        Notify::setDefault($request->default);
        Notify::setFallback($request->fallback);

        // برای ماندگاری بین requests می‌توانید در یک جدول settings ذخیره کنید:
        // Setting::set('Notify_default', $request->default);
        // Setting::set('Notify_fallback', $request->fallback);

        return back()->with('success', 'پنل پیش‌فرض با موفقیت تغییر کرد.');
    }

    /**
     * ارسال پیامک آزمایشی
     */
    public function sendTest(Request $request): RedirectResponse
    {
        $request->validate([
            'phone'    => 'required|string|min:10|max:13',
            'driver'   => 'required|in:smsir,ghasedak,mediana,melipayamak,ippanel',
            'message'  => 'nullable|string|max:500',
        ]);

        $response = Notify::message(
            $request->phone,
            $request->message ?? 'این یک پیامک آزمایشی است 🎉'
        )->via($request->driver)->send();

        if ($response->isSuccessful()) {
            return back()->with('success', 'پیامک آزمایشی با موفقیت ارسال شد!');
        }

        return back()->with('error', 'خطا در ارسال: ' . $response->message);
    }
}


// =====================================================
// نمونه استفاده در یک سرویس OTP
// =====================================================

namespace App\Services;

use Extenbox\Notify\Facades\Notify;

class OtpService
{
    public function send(string $phone, string $code): bool
    {
        $response = Notify::message($phone, "code: {$code}")
            ->via('smsir')
            ->type('pattern', config('otp.template_id'), [
                'code' => $code,
            ])
            ->send();

        return $response->isSuccessful();
    }
}


// =====================================================
// نمونه استفاده در Laravel Notification
// =====================================================

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Extenbox\Notify\Channels\NotifyChannel;
use Extenbox\Notify\Channels\NotifyMessage;

class OrderShipped extends Notification
{
    public function __construct(private string $orderCode) {}

    public function via(mixed $notifiable): array
    {
        return [NotifyChannel::class];
    }

    public function toNotify(mixed $notifiable): NotifyMessage
    {
        return NotifyMessage::create("سفارش {$this->orderCode} شما ارسال شد")
            ->via('smsir', '30007732...');
    }
}
