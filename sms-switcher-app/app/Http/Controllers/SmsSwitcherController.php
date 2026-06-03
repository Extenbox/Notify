<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Services\SmsService;
use InvalidArgumentException;
use Throwable;

class SmsSwitcherController extends Controller
{
    public function __construct(
        private readonly SmsService $smsService
    ) {}

    public function index(): View
    {
        return view('sms.index', [
            'providers'       => $this->smsService->getProviders(),
            'currentProvider' => $this->smsService->getDefaultProvider(),
            'providerFields'  => $this->smsService->getAllProviderFields(),
        ]);
    }

    public function settings(): View
    {
        return view('sms.settings', [
            'providers'       => $this->smsService->getProviders(),
            'storedSettings'  => $this->smsService->getStoredSettings(),
            'providerFields'  => $this->smsService->getAllProviderFields(),
        ]);
    }

    public function getSettings(): JsonResponse
    {
        return response()->json([
            'success'  => true,
            'settings' => $this->smsService->getStoredSettings(),
            'fields'   => $this->smsService->getAllProviderFields(),
        ]);
    }

    public function saveSetting(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'driver'      => ['required', 'string'],
            'config'      => ['required', 'array'],
            'is_active'   => ['boolean'],
            'is_default'  => ['boolean'],
            'is_fallback' => ['boolean'],
        ]);

        try {
            $this->smsService->saveProviderSetting(
                driver: $validated['driver'],
                config: $validated['config'],
                options: [
                    'is_active'   => $validated['is_active'] ?? true,
                    'is_default'  => $validated['is_default'] ?? false,
                    'is_fallback' => $validated['is_fallback'] ?? false,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'تنظیمات با موفقیت ذخیره شد.',
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone'    => ['required', 'string', 'min:10', 'max:15'],
            'message'  => ['required', 'string', 'max:500'],
            'provider' => ['nullable', 'string'],
        ]);

        try {
            $response = $this->smsService->send(
                phone: $validated['phone'],
                message: $validated['message'],
                provider: $validated['provider'] ?: null
            );

            return $this->successResponse(
                $response->isSuccessful() ? 'پیام با موفقیت ارسال شد.' : 'خطا در ارسال پیام.',
                ['provider' => $validated['provider'] ?? 'default', 'response' => $response->toArray()]
            );

        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('خطا: ' . $e->getMessage(), 500);
        }
    }

    public function sendWithFallback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone'   => ['required', 'string', 'min:10', 'max:15'],
            'message' => ['required', 'string', 'max:500'],
        ]);

        try {
            $response = $this->smsService->sendWithFallback(
                phone: $validated['phone'],
                message: $validated['message']
            );

            return $this->successResponse(
                $response->isSuccessful() ? 'پیام با موفقیت ارسال شد.' : 'خطا در ارسال پیام.',
                ['response' => $response->toArray()]
            );

        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('خطا: ' . $e->getMessage(), 500);
        }
    }

    private function successResponse(string $message, array $data = [], int $code = 200): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, ...$data], $code);
    }

    private function errorResponse(string $message, int $code = 500): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $code);
    }
}