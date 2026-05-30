<?php

namespace Extenbox\Notify\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Extenbox\Notify\Facades\Notifak;
use Extenbox\Notify\Models\NotifakLog;
use Extenbox\Notify\Models\NotifakProvider;

/**
 * کنترلر مدیریت پنل پیامک
 *
 * می‌توانید این کنترلر را در پروژه خود extend کنید
 * یا از متدهای آن به صورت مستقیم استفاده کنید.
 *
 * نمونه استفاده در routes/web.php:
 *   Route::post('/admin/sms/settings', [NotifakController::class, 'updateSettings']);
 *   Route::post('/admin/sms/test',     [NotifakController::class, 'testSend']);
 *   Route::get('/admin/sms/logs',      [NotifakController::class, 'logs']);
 */
class NotifakController extends Controller
{
    /**
     * دریافت لیست پنل‌های پیامکی و وضعیت آن‌ها
     */
    public function index(): JsonResponse
    {
        $drivers = ['mediana', 'melipayamak', 'ghasedak', 'smsir', 'ippanel'];

        $providers = NotifakProvider::whereIn('driver', $drivers)->get()
            ->keyBy('driver');

        $result = array_map(function ($driver) use ($providers) {
            $provider = $providers->get($driver);
            $config   = $provider?->config ?? [];

            // مخفی‌سازی api_key در خروجی
            if (isset($config['api_key'])) {
                $config['api_key'] = $this->maskSecret($config['api_key']);
            }
            if (isset($config['password'])) {
                $config['password'] = $this->maskSecret($config['password']);
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
            'default'  => config('notifak.default'),
            'fallback' => config('notifak.fallback'),
            'source'   => config('notifak.config_source'),
        ]);
    }

    /**
     * به‌روزرسانی تنظیمات یک پنل
     *
     * POST /notifak/settings
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

        // اگر api_key ماسک‌شده بود، تغییر نده
        if (isset($validated['api_key']) && str_contains($validated['api_key'], '***')) {
            unset($validated['api_key']);
        }
        if (isset($validated['password']) && str_contains($validated['password'], '***')) {
            unset($validated['password']);
        }

        // merge با تنظیمات موجود
        $existing = NotifakProvider::getConfig($driver);
        $config   = array_filter(
            array_merge($existing, $validated),
            fn($v) => $v !== null
        );

        NotifakProvider::setConfig($driver, $config);

        // همچنین در runtime هم اعمال کن
        Notifak::configureDriver($driver, $config);

        return response()->json([
            'success' => true,
            'message' => "تنظیمات درایور {$driver} ذخیره شد",
        ]);
    }

    /**
     * تنظیم پنل پیش‌فرض و پشتیبان
     *
     * POST /notifak/settings/defaults
     * body: { default, fallback }
     */
    public function updateDefaults(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'default'  => 'required|in:mediana,melipayamak,ghasedak,smsir,ippanel',
            'fallback' => 'nullable|in:mediana,melipayamak,ghasedak,smsir,ippanel',
        ]);

        // ذخیره در config runtime
        Notifak::setDefault($validated['default']);
        Notifak::setFallback($validated['fallback'] ?? null);

        // اگر می‌خواهید در .env یا db ذخیره کنید، اینجا کد اضافه کنید
        // مثلا: Setting::set('notifak_default', $validated['default']);

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
     * POST /notifak/test
     * body: { phone, driver?, sender?, message? }
     */
    public function testSend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone'   => 'required|string',
            'driver'  => 'nullable|in:mediana,melipayamak,ghasedak,smsir,ippanel',
            'sender'  => 'nullable|string',
            'message' => 'nullable|string|max:500',
        ]);

        $phone   = $validated['phone'];
        $message = $validated['message'] ?? 'پیامک آزمایشی از Notifak - ' . now()->format('H:i:s');

        $pending = Notifak::send($phone, $message);

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
     * GET /notifak/logs
     */
    public function logs(Request $request): JsonResponse
    {
        $query = NotifakLog::query()->latest();

        if ($request->filled('provider')) {
            $query->provider($request->provider);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs  = $query->paginate($request->get('per_page', 20));
        $stats = NotifakLog::stats();

        return response()->json([
            'success' => true,
            'stats'   => $stats,
            'logs'    => $logs,
        ]);
    }

    /**
     * حذف لاگ‌های قدیمی
     *
     * DELETE /notifak/logs
     * body: { days: 30 }
     */
    public function clearLogs(Request $request): JsonResponse
    {
        $days    = $request->input('days', 30);
        $deleted = NotifakLog::where('created_at', '<', now()->subDays($days))->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} رکورد لاگ حذف شد",
        ]);
    }

    private function maskSecret(string $value): string
    {
        if (strlen($value) <= 6) {
            return '***';
        }
        return substr($value, 0, 3) . str_repeat('*', strlen($value) - 6) . substr($value, -3);
    }
}
