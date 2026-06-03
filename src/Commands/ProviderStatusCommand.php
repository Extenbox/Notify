<?php

namespace Extenbox\Notify\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProviderStatusCommand extends Command
{
    protected $signature = 'notify:status';

    protected $aliases = ['Notify:status'];

    protected $description = 'نمایش وضعیت پنل‌های پیامکی تنظیم‌شده';

    public function handle(): int
    {
        $config  = config('Notify');
        $drivers = ['mediana', 'melipayamak', 'ghasedak', 'smsir', 'ippanel'];

        $this->newLine();
        $this->line('  <fg=cyan;options=bold>📱 Notify - وضعیت پنل‌های پیامکی</>');
        $this->newLine();

        $this->line("  منبع تنظیمات : <fg=yellow>{$config['config_source']}</>");
        $this->line("  پنل پیش‌فرض  : <fg=green>{$config['default']}</>");
        $this->line("  پنل پشتیبان  : <fg=yellow>" . ($config['fallback'] ?? 'ندارد') . "</>");
        $this->newLine();

        $rows = [];
        foreach ($drivers as $driver) {
            $driverConfig = $config['drivers'][$driver] ?? [];
            $isDefault    = $config['default'] === $driver ? '✅ پیش‌فرض' : '';
            $isFallback   = $config['fallback'] === $driver ? '🔁 پشتیبان' : '';
            $hasKey       = !empty($driverConfig['api_key'] ?? $driverConfig['username'] ?? '')
                ? '<fg=green>✓ تنظیم‌شده</>'
                : '<fg=red>✗ تنظیم‌نشده</>';

            $rows[] = [
                $driver,
                $driverConfig['sender'] ?? '-',
                $hasKey,
                trim("$isDefault $isFallback") ?: '-',
            ];
        }

        $this->table(
            ['درایور', 'شماره ارسال', 'وضعیت API', 'نقش'],
            $rows
        );

        // آمار لاگ
        if ($config['log']['enabled'] ?? true) {
            try {
                $table = $config['log']['table'] ?? 'Notify_logs';
                $total  = DB::table($table)->count();
                $sent   = DB::table($table)->where('status', 'sent')->count();
                $failed = DB::table($table)->where('status', 'failed')->count();

                $this->newLine();
                $this->line("  📊 آمار ارسال: کل={$total}  موفق={$sent}  ناموفق={$failed}");
            } catch (\Throwable) {
                // جدول هنوز migrate نشده
            }
        }

        $this->newLine();
        return self::SUCCESS;
    }
}
