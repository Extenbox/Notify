<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsSwitcherController;
use App\Services\SmsService;

// صفحه اصلی
Route::get('/', [SmsSwitcherController::class, 'index'])->name('home');

// تنظیمات
Route::get('/settings', [SmsSwitcherController::class, 'settings'])->name('settings');
Route::get('/get-settings', [SmsSwitcherController::class, 'getSettings']);
Route::post('/save-setting', [SmsSwitcherController::class, 'saveSetting']);

// ارسال
Route::post('/send-sms', [SmsSwitcherController::class, 'send']);
Route::post('/send-sms-fallback', [SmsSwitcherController::class, 'sendWithFallback']);

// تست
Route::get('/test-sms', function (SmsService $service) {
    $service->loadSettingsFromDatabase();
    try {
        $response = $service->send('09123456789', 'تست');
        return response()->json(['success' => $response->isSuccessful(), 'response' => $response->toArray()]);
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
});