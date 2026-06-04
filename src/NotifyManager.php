<?php

namespace Extenbox\Notify;

use Illuminate\Support\Facades\DB;
use Extenbox\Notify\Contracts\SmsDriver;
use Extenbox\Notify\Contracts\SmsResponse;
use Extenbox\Notify\Drivers\Ghasedak;
use Extenbox\Notify\Drivers\IpPanel;
use Extenbox\Notify\Drivers\Mediana;
use Extenbox\Notify\Drivers\MeliPayamak;
use Extenbox\Notify\Drivers\SmsIr;
use Extenbox\Notify\Exceptions\DriverNotFoundException;
use Extenbox\Notify\Support\Config;

class NotifyManager
{
    protected array $drivers = [];

    protected array $driverMap = [
        'mediana'     => Mediana::class,
        'melipayamak' => MeliPayamak::class,
        'ghasedak'    => Ghasedak::class,
        'smsir'       => SmsIr::class,
        'ippanel'     => IpPanel::class,
    ];

    public function __construct(protected array $config = [])
    {
        if ($this->config === []) {
            $this->config = Config::load();
        }
    }

    public function sms(string|array $to, string $message): PendingSms
    {
        return new PendingSms($this, $to, $message);
    }

    public function flash(string|array $to, string $patternCode, array $variables = []): PendingSms
    {
        return new PendingSms($this, $to, '', $patternCode, $variables);
    }

    public function message(string|array $to, string $message): PendingSms
    {
        return $this->sms($to, $message);
    }

    /**
     * اجرای ارسال نهایی (توسط PendingSms صدا زده می‌شود)
     */
    public function dispatch(PendingSms $pending): SmsResponse
    {
        $providerName = $pending->getProvider() ?? $this->getDefaultDriver();

        $response = $this->attemptSend($pending, $providerName);

        // اگر ناموفق بود و fallback تعریف شده، با fallback امتحان می‌کنیم
        if (!$response->isSuccessful()) {
            $fallback = $this->getFallbackDriver();
            if ($fallback && $fallback !== $providerName) {
                $fallbackResponse = $this->attemptSend($pending, $fallback);
                if ($fallbackResponse->isSuccessful()) {
                    $this->log($pending, $fallbackResponse, $fallback . ' (fallback)');
                    return $fallbackResponse;
                }
            }
        }

        $this->log($pending, $response, $providerName);

        return $response;
    }

    protected function attemptSend(PendingSms $pending, string $providerName): SmsResponse
    {
        try {
            $driver = $this->driver($providerName);

            if ($pending->getSender()) {
                $driver->setSender($pending->getSender());
            }

            if ($pending->getPatternCode() !== null) {
                return $driver->sendPattern(
                    $pending->getTo(),
                    $pending->getPatternCode(),
                    $pending->getVariables()
                );
            }

            return $driver->sendNormal($pending->getTo(), $pending->getMessage());

        } catch (DriverNotFoundException $e) {
            return SmsResponse::failure($e->getMessage());
        } catch (\Throwable $e) {
            return SmsResponse::failure('خطای داخلی: ' . $e->getMessage());
        }
    }

    /**
     * دریافت یا ساخت یک instance از درایور
     */
    public function driver(string $name): SmsDriver
    {
        if (!isset($this->drivers[$name])) {
            $this->drivers[$name] = $this->createDriver($name);
        }

        return $this->drivers[$name];
    }

    protected function createDriver(string $name): SmsDriver
    {
        if (!isset($this->driverMap[$name])) {
            throw DriverNotFoundException::for($name);
        }

        $config = $this->getDriverConfig($name);
        $class  = $this->driverMap[$name];

        return new $class($config);
    }

    protected function getDriverConfig(string $name): array
    {
        $source = $this->config['config_source'] ?? 'config';

        if ($source === 'database') {
            return $this->withSharedDriverOptions($this->getConfigFromDatabase($name));
        }

        return $this->withSharedDriverOptions($this->config['drivers'][$name] ?? []);
    }

    protected function withSharedDriverOptions(array $driverConfig): array
    {
        if (!array_key_exists('ssl_verify', $driverConfig) && !array_key_exists('sslverify', $driverConfig)) {
            $driverConfig['ssl_verify'] = $this->config['ssl_verify'] ?? true;
        }

        if (array_key_exists('ssl_verify', $driverConfig) && $driverConfig['ssl_verify'] === null) {
            $driverConfig['ssl_verify'] = $this->config['ssl_verify'] ?? true;
        } elseif (array_key_exists('sslverify', $driverConfig) && $driverConfig['sslverify'] === null) {
            $driverConfig['sslverify'] = $this->config['ssl_verify'] ?? true;
        }

        return $driverConfig;
    }

    protected function getConfigFromDatabase(string $name): array
    {
        $table = $this->config['table'] ?? 'Notify_providers';

        try {
            if (!class_exists(DB::class)) {
                return $this->config['drivers'][$name] ?? [];
            }

            $row = DB::table($table)
                ->where('driver', $name)
                ->where('is_active', true)
                ->first();

            if (!$row) {
                // fallback به config فایل
                return $this->config['drivers'][$name] ?? [];
            }

            return json_decode($row->config ?? '{}', true) ?? [];
        } catch (\Throwable) {
            // اگر جدول موجود نبود، از config فایل استفاده می‌کند
            return $this->config['drivers'][$name] ?? [];
        }
    }

    /**
     * تنظیم مستقیم یک درایور از طریق آرایه
     *
     * Notify::configureDriver('smsir', [
     *     'api_key' => 'xxx',
     *     'sender'  => '3000...',
     * ]);
     */
    public function configureDriver(string $name, array $config): static
    {
        $this->config['drivers'][$name] = array_merge(
            $this->config['drivers'][$name] ?? [],
            $config
        );

        // ریست instance کش تا تنظیمات جدید اعمال شود
        unset($this->drivers[$name]);

        return $this;
    }

    /**
     * ذخیره تنظیمات در دیتابیس
     *
     * Notify::saveConfigToDatabase('smsir', ['api_key' => 'xxx', 'sender' => '3000...']);
     */
    public function saveConfigToDatabase(string $name, array $config): bool
    {
        $table = $this->config['table'] ?? 'Notify_providers';

        try {
            if (!class_exists(DB::class)) {
                return false;
            }

            DB::table($table)->updateOrInsert(
                ['driver' => $name],
                [
                    'config'     => json_encode($config),
                    'is_active'  => true,
                    'updated_at' => $this->now(),
                    'created_at' => $this->now(),
                ]
            );

            // ریست instance کش
            unset($this->drivers[$name]);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function getDefaultDriver(): string
    {
        return $this->config['default'] ?? 'smsir';
    }

    public function getFallbackDriver(): ?string
    {
        return $this->config['fallback'] ?? null;
    }

    public function shouldAutoSend(): bool
    {
        return (bool) ($this->config['auto_send'] ?? true);
    }

    /**
     * تنظیم پنل پیش‌فرض در runtime
     */
    public function setDefault(string $name): static
    {
        $this->config['default'] = $name;
        return $this;
    }

    /**
     * تنظیم پنل پشتیبان در runtime
     */
    public function setFallback(?string $name): static
    {
        $this->config['fallback'] = $name;
        return $this;
    }

    /**
     * ثبت درایور سفارشی
     *
     * Notify::extend('mypanel', MyPanelDriver::class);
     */
    public function extend(string $name, string $driverClass): static
    {
        $this->driverMap[$name] = $driverClass;
        return $this;
    }

    /**
     * دریافت لیست درایورهای ثبت‌شده
     */
    public function getAvailableDrivers(): array
    {
        return array_keys($this->driverMap);
    }

    protected function log(PendingSms $pending, SmsResponse $response, string $provider): void
    {
        if (!($this->config['log']['enabled'] ?? true)) {
            return;
        }

        $table = $this->config['log']['table'] ?? 'Notify_logs';

        try {
            if (!class_exists(DB::class)) {
                return;
            }

            DB::table($table)->insert([
                'provider'   => $provider,
                'to'         => is_array($pending->getTo())
                    ? implode(',', $pending->getTo())
                    : $pending->getTo(),
                'type'       => $pending->getPatternCode() !== null ? 'flash' : 'sms',
                'message'    => $pending->getMessage(),
                'status'     => $response->isSuccessful() ? 'sent' : 'failed',
                'response'   => json_encode($response->toArray()),
                'created_at' => $this->now(),
                'updated_at' => $this->now(),
            ]);
        } catch (\Throwable) {
            // لاگ silent باشد تا ارسال مختل نشود
        }
    }

    protected function now(): string|object
    {
        return function_exists('now') ? now() : date('Y-m-d H:i:s');
    }
}
