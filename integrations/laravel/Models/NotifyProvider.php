<?php

namespace Extenbox\Notify\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل تنظیمات پنل‌های پیامکی ذخیره‌شده در دیتابیس
 *
 * @property int         $id
 * @property string      $driver
 * @property array       $config
 * @property bool        $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class NotifyProvider extends Model
{
    protected $table = 'Notify_providers';

    protected $fillable = [
        'driver',
        'config',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * کلیدهای حساس که در دیتابیس رمزنگاری می‌شوند.
     *
     * @var array<int, string>
     */
    protected static array $secretConfigKeys = ['api_key', 'password', 'username', 'secret', 'token'];

    public function getConfigAttribute($value): array
    {
        if (is_array($value)) {
            $config = $value;
        } else {
            $decoded = json_decode($value ?? '{}', true);
            $config = is_array($decoded) ? $decoded : [];
        }

        return static::revealConfig($config);
    }

    public function setConfigAttribute($value): void
    {
        if (is_array($value)) {
            $config = $value;
        } else {
            $decoded = json_decode($value ?? '{}', true);
            $config = is_array($decoded) ? $decoded : [];
        }

        $this->attributes['config'] = json_encode(static::protectConfig($config), JSON_UNESCAPED_UNICODE);
    }

    public static function protectConfig(array $config): array
    {
        if (!function_exists('encrypt')) {
            return $config;
        }

        foreach (static::$secretConfigKeys as $key) {
            if (!array_key_exists($key, $config)) {
                continue;
            }

            $secret = $config[$key];
            if (!is_string($secret) || $secret === '' || static::isEncryptedValue($secret)) {
                continue;
            }

            try {
                $config[$key] = encrypt($secret);
            } catch (\Throwable $e) {
                // اگر رمزنگاری ممکن نبود، مقدار دست‌نخورده باقی می‌ماند
            }
        }

        return $config;
    }

    public static function revealConfig(array $config): array
    {
        if (!function_exists('decrypt')) {
            return $config;
        }

        foreach (static::$secretConfigKeys as $key) {
            if (!array_key_exists($key, $config)) {
                continue;
            }

            $secret = $config[$key];
            if (!is_string($secret) || $secret === '' || !static::isEncryptedValue($secret)) {
                continue;
            }

            try {
                $config[$key] = decrypt($secret);
            } catch (\Throwable $e) {
                // سازگاری با مقادیر قدیمی/plaintext
            }
        }

        return $config;
    }

    protected static function isEncryptedValue(string $value): bool
    {
        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            return false;
        }

        $payload = json_decode($decoded, true);

        return is_array($payload)
            && isset($payload['iv'], $payload['value'], $payload['mac']);
    }

    /**
     * دریافت تنظیمات یک درایور خاص
     */
    public static function getConfig(string $driver): array
    {
        $provider = static::where('driver', $driver)
            ->where('is_active', true)
            ->first();

        return $provider?->config ?? [];
    }

    /**
     * ذخیره یا به‌روزرسانی تنظیمات درایور
     */
    public static function setConfig(string $driver, array $config): static
    {
        return static::updateOrCreate(
            ['driver' => $driver],
            [
                'config'    => $config,
                'is_active' => true,
            ]
        );
    }

    /**
     * فعال‌سازی یک درایور
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * غیرفعال‌سازی یک درایور
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }
}
