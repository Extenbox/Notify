<?php

namespace Extenbox\Notify\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Extenbox\Notify\Facades\Notify;
use Extenbox\Notify\Models\NotifyLog;
use Extenbox\Notify\Models\NotifyProvider;

/**
 * کنترلر مدیریت پنل پیامک
 *
 * هشدار امنیتی: این کنترلر احراز هویت/مجوز اعمال نمی‌کند.
 * routeها را فقط برای کاربران مجاز (ادمین) و پشت middleware مناسب
 * (auth, can/policy, throttle و ...) ثبت کنید.
 *
 * نمونه استفاده در routes/web.php:
 *   Route::middleware(['auth', 'can:manage-sms'])->group(function () {
 *       Route::post('/admin/sms/settings', [NotifyController::class, 'updateSettings']);
 *       Route::post('/admin/sms/test',     [NotifyController::class, 'testSend']);
 *       Route::get('/admin/sms/logs',      [NotifyController::class, 'logs']);
 *   });
 */
class NotifyController extends Controller
{
    /**
     * دریافت لیست پنل‌های پیامکی و وضعیت آن‌ها
     */
    public function index(): JsonResponse
    {
        $drivers = ['mediana', 'melipayamak', 'ghasedak', 'smsir', 'ippanel'];

        $providers = NotifyProvider::whereIn('driver', $drivers)->get()
            ->keyBy('driver');

        $result = array_map(function ($driver) use ($providers) {
            $provider = $providers->get($driver);
            $config   = $provider?->config ?? [];

            // مخفی‌سازی مقادیر حساس در خروجی
            foreach (['api_key', 'password', 'username', 'secret', 'token'] as $secretKey) {
                if (array_key_exists($secretKey, $config)) {
                    $config[$secretKey] = $this->maskSecret((string) $config[$secretKey]);
                }
            }

            return [
                'driver'    => $driver,
                'is_active' => $provider?->is_active ?? false,
                'config'    => $config,
            ];
        }, $drivers);

        return response()->json([
            'success'  => true,
            'drivers'  => array_values($result),
            'default'  => config('Notify.default'),
            'fallback' => config('Notify.fallback'),
            'source'   => config('Notify.config_source'),
        ]);
    }

    /**
     * به‌روزرسانی تنظیمات یک پنل
     *
     * POST /Notify/settings
     * body: { driver, api_key, sender, ... }
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'driver'    => 'required|in:mediana,melipayamak,ghasedak,smsir,ippanel',
            'api_key'   => 'nullable|string',
            'username'  => 'nullable|string',
            'password'  => 'nullable|string',
            'sender'    => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $driver = $validated['driver'];
        unset($validated['driver']);

        // اگر مقدار حساس ماسک‌شده/خالی بود، تغییر نده
        foreach (['api_key', 'password', 'username', 'secret', 'token'] as $secretKey) {
            if (!array_key_exists($secretKey, $validated)) {
                continue;
            }

            $value = $validated[$secretKey];
            if ($value === null || $value === '' || $this->isMaskedSecret((string) $value)) {
                unset($validated[$secretKey]);
            }
        }

        if (array_key_exists('is_active', $validated)) {
            $validated['is_active'] = (bool) $validated['is_active'];
        }

        // merge با تنظیمات موجود
        $existing = NotifyProvider::getConfig($driver);
        $config   = array_filter(
            array_merge($existing, $validated),
            fn($v) => $v !== null
        );

        NotifyProvider::setConfig($driver, $config);

        // همچنین در runtime هم اعمال کن
        Notify::configureDriver($driver, $config);

        return response()->json([
            'success' => true,
            'message' => "تنظیمات درایور {$driver} ذخیره شد",
        ]);
    }

    /**
     * تنظیم پنل پیش‌فرض و پشتیبان
     *
     * POST /Notify/settings/defaults
     * body: { default, fallback }
     */
    public function updateDefaults(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'default'  => 'required|in:mediana,melipayamak,ghasedak,smsir,ippanel',
            'fallback' => 'nullable|in:mediana,melipayamak,ghasedak,smsir,ippanel',
        ]);

        // ذخیره در config runtime
        Notify::setDefault($validated['default']);
        Notify::setFallback($validated['fallback'] ?? null);

        // اگر می‌خواهید در .env یا db ذخیره کنید، اینجا کد اضافه کنید
        // مثلا: Setting::set('Notify_default', $validated['default']);

        return response()->json([
            'success' => true,
            'message' => 'پنل پیش‌فرض به‌روزرسانی شد',
            'default' => $validated['default'],
            'fallback' => $validated['fallback'],
        ]);
    }

    /**
     * ارسال پیامک آزمایشی
     *
     * POST /Notify/test
     * body: { phone, driver?, sender?, message? }
     */
    public function testSend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone'   => 'required|string|max:32',
            'driver'  => 'nullable|in:mediana,melipayamak,ghasedak,smsir,ippanel',
            'sender'  => 'nullable|string|max:32',
            'message' => 'nullable|string|max:500',
        ]);

        $phone   = $validated['phone'];
        $message = $validated['message'] ?? 'پیامک آزمایشی از Notify - ' . now()->format('H:i:s');

        $pending = Notify::sms($phone, $message);

        if (!empty($validated['driver'])) {
            $pending->via($validated['driver'], $validated['sender'] ?? null);
        }

        $response = $pending->send();

        return response()->json([
            'success'  => $response->isSuccessful(),
            'message'  => $response->message,
            'data'     => $response->data,
        ], $response->isSuccessful() ? 200 : 422);
    }

    /**
     * مشاهده لاگ ارسال‌ها
     *
     * GET /Notify/logs
     */
    public function logs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => 'nullable|in:mediana,melipayamak,ghasedak,smsir,ippanel',
            'status'   => 'nullable|in:sent,failed',
            'date'     => 'nullable|date',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = NotifyLog::query()->latest();

        if (!empty($validated['provider'])) {
            $query->provider($validated['provider']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['date'])) {
            $query->whereDate('created_at', $validated['date']);
        }

        $logs = $query->paginate((int) ($validated['per_page'] ?? 20));
        $logs->getCollection()->transform(function ($log) {
            $log->to = $this->maskRecipientForDisplay((string) ($log->to ?? ''));
            $log->message = !empty($log->message) ? '[redacted]' : null;

            if (is_array($log->response)) {
                $log->response = [
                    'success' => $log->response['success'] ?? ($log->status === 'sent'),
                    'message' => $log->response['message'] ?? null,
                    'status_code' => $log->response['status_code'] ?? null,
                ];
            } else {
                $log->response = null;
            }

            return $log;
        });

        $stats = NotifyLog::stats();

        return response()->json([
            'success' => true,
            'stats'   => $stats,
            'logs'    => $logs,
        ]);
    }

    /**
     * حذف لاگ‌های قدیمی
     *
     * DELETE /Notify/logs
     * body: { days: 30 }
     */
    public function clearLogs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'days' => 'nullable|integer|min:1|max:3650',
        ]);

        $days = (int) ($validated['days'] ?? 30);
        $deleted = NotifyLog::where('created_at', '<', now()->subDays($days))->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} رکورد لاگ حذف شد",
        ]);
    }

    private function maskSecret(string $value): string
    {
        if ($value === '') {
            return '';
        }

        return '********';
    }

    private function isMaskedSecret(string $value): bool
    {
        return (bool) preg_match('/^\*+$/', $value);
    }

    private function maskRecipientForDisplay(string $value): string
    {
        $parts = array_filter(array_map('trim', explode(',', $value)));

        if ($parts === []) {
            $parts = [$value];
        }

        $masked = array_map(function (string $part): string {
            $digits = preg_replace('/\D/', '', $part) ?? '';
            $length = strlen($digits);

            if ($length === 0) {
                return '***';
            }

            if ($length <= 4) {
                return str_repeat('*', $length);
            }

            return str_repeat('*', $length - 4) . substr($digits, -4);
        }, $parts);

        return implode(',', $masked);
    }
}
