<?php

namespace App\Services;

use Extenbox\Notify\Facades\Notify;
use Extenbox\Notify\Contracts\SmsResponse;
use App\Models\SmsProviderSetting;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;

class SmsService
{
    private const LABELS = [
        'ghasedak' => 'قاصدک',
        'mediana' => 'مدیانا',
        'melipayamak' => 'ملی پیامک',
        'smsir' => 'SMS.ir',
        'ippanel' => 'IP Panel',
        'parsgreen' => 'پارس گرین',
    ];

    private const PROVIDER_FIELDS = [
        'ghasedak' => [
            'api_key' => ['label' => 'API Key', 'type' => 'text', 'required' => true],
            'sender' => ['label' => 'شماره ارسال', 'type' => 'text', 'required' => false],
        ],
        'mediana' => [
            'api_key' => ['label' => 'API Key', 'type' => 'text', 'required' => true],
            'sender' => ['label' => 'شماره ارسال', 'type' => 'text', 'required' => false],
        ],
        'melipayamak' => [
            'username' => ['label' => 'Username', 'type' => 'text', 'required' => true],
            'password' => ['label' => 'Password', 'type' => 'password', 'required' => true],
            'sender' => ['label' => 'شماره ارسال', 'type' => 'text', 'required' => false],
        ],
        'smsir' => [
            'api_key' => ['label' => 'API Key', 'type' => 'text', 'required' => true],
            'secret_key' => ['label' => 'Secret Key', 'type' => 'text', 'required' => true],
            'sender' => ['label' => 'شماره ارسال', 'type' => 'text', 'required' => false],
        ],
        'ippanel' => [
            'api_key' => ['label' => 'API Key', 'type' => 'text', 'required' => true],
            'sender' => ['label' => 'شماره ارسال', 'type' => 'text', 'required' => false],
        ],

        'parsgreen' => [
            'signature' => ['label' => 'Signature', 'type' => 'text', 'required' => true],
            'sender' => ['label' => 'شماره ارسال', 'type' => 'text', 'required' => false],
            'pattern_id' => ['label' => 'Pattern ID', 'type' => 'number', 'required' => false],
        ],
    ];

    public function getProviders(): array
    {
        return self::LABELS;
    }

    public function getProviderFields(string $provider): array
    {
        return self::PROVIDER_FIELDS[$provider] ?? [];
    }

    public function getAllProviderFields(): array
    {
        return self::PROVIDER_FIELDS;
    }

    public function getStoredSettings(): array
    {
        $settings = SmsProviderSetting::all()->keyBy('driver');

        $providers = [];
        foreach (self::LABELS as $driver => $label) {
            $stored = $settings->get($driver);
            $providers[$driver] = [
                'label' => $label,
                'config' => $stored?->config ?? [],
                'is_active' => $stored?->is_active ?? false,
                'is_default' => $stored?->is_default ?? false,
                'is_fallback' => $stored?->is_fallback ?? false,
                'is_stored' => !is_null($stored),
            ];
        }

        return $providers;
    }

    public function saveProviderSetting(string $driver, array $config, array $options = []): void
    {
        DB::transaction(function () use ($driver, $config, $options) {
            if (!empty($options['is_default'])) {
                SmsProviderSetting::where('is_default', true)->update(['is_default' => false]);
            }

            if (!empty($options['is_fallback'])) {
                SmsProviderSetting::where('is_fallback', true)->update(['is_fallback' => false]);
            }

            SmsProviderSetting::updateOrCreate(
                ['driver' => $driver],
                [
                    'label' => self::LABELS[$driver] ?? $driver,
                    'config' => $config,
                    'is_active' => $options['is_active'] ?? true,
                    'is_default' => $options['is_default'] ?? false,
                    'is_fallback' => $options['is_fallback'] ?? false,
                ]
            );

            if (!empty($config)) {
                Notify::configureDriver($driver, $config);
            }

            if (!empty($options['is_default'])) {
                Notify::setDefault($driver);
            }

            if (!empty($options['is_fallback'])) {
                Notify::setFallback($driver);
            }
        });
    }

    public function loadSettingsFromDatabase(): void
    {
        $settings = SmsProviderSetting::active()->get();

        foreach ($settings as $setting) {
            if (!empty($setting->config)) {
                Notify::configureDriver($setting->driver, $setting->config);
            }

            if ($setting->is_default) {
                Notify::setDefault($setting->driver);
            }

            if ($setting->is_fallback) {
                Notify::setFallback($setting->driver);
            }
        }
    }

    public function getDefaultProvider(): string
    {
        $stored = SmsProviderSetting::default()->first();
        return $stored?->driver ?? Notify::getDefaultDriver();
    }

    public function send(string $phone, string $message, ?string $provider = null): SmsResponse
    {
        $this->validatePhone($phone);
        $this->validateMessage($message);

        $this->loadSettingsFromDatabase();

        $sms = Notify::send($phone, $message);

        if ($provider) {
            $sms->via($provider);
        }

        return $sms->send();
    }

    public function sendWithFallback(string $phone, string $message): SmsResponse
    {
        $this->validatePhone($phone);
        $this->validateMessage($message);

        $this->loadSettingsFromDatabase();

        return Notify::send($phone, $message)->send();
    }

    public function getActiveProviders(): array
    {
        $settings = SmsProviderSetting::active()
            ->whereNotNull('config')
            ->get()
            ->keyBy('driver');

        $providers = [];
        foreach ($settings as $driver => $setting) {
            if (!empty($setting->config) && count(array_filter($setting->config)) > 0) {
                $providers[$driver] = self::LABELS[$driver] ?? $driver;
            }
        }

        return $providers;
    }

    private function validatePhone(string $phone): void
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            throw new InvalidArgumentException('شماره موبایل باید بین ۱۰ تا ۱۵ رقم باشد.');
        }
    }

    private function validateMessage(string $message): void
    {
        $message = trim($message);
        if (empty($message)) {
            throw new InvalidArgumentException('متن پیام نمی‌تواند خالی باشد.');
        }
        if (mb_strlen($message) > 500) {
            throw new InvalidArgumentException('متن پیام نمی‌تواند بیشتر از ۵۰۰ کاراکتر باشد.');
        }
    }
}