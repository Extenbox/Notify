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
class NotifakProvider extends Model
{
    protected $table = 'notifak_providers';

    protected $fillable = [
        'driver',
        'config',
        'is_active',
    ];

    protected $casts = [
        'config'    => 'array',
        'is_active' => 'boolean',
    ];

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
