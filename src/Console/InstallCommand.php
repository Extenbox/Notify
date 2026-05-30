<?php

namespace Extenbox\Notify\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature   = 'notifak:install';
    protected $description = 'نصب و راه‌اندازی پکیج Notifak';

    public function handle(): int
    {
        $this->info('');
        $this->info('  ███╗   ██╗ ██████╗ ████████╗██╗███████╗ █████╗ ██╗  ██╗');
        $this->info('  ████╗  ██║██╔═══██╗╚══██╔══╝██║██╔════╝██╔══██╗██║ ██╔╝');
        $this->info('  ██╔██╗ ██║██║   ██║   ██║   ██║█████╗  ███████║█████╔╝ ');
        $this->info('  ██║╚██╗██║██║   ██║   ██║   ██║██╔══╝  ██╔══██║██╔═██╗ ');
        $this->info('  ██║ ╚████║╚██████╔╝   ██║   ██║██║     ██║  ██║██║  ██╗');
        $this->info('  ╚═╝  ╚═══╝ ╚═════╝    ╚═╝   ╚═╝╚═╝     ╚═╝  ╚═╝╚═╝  ╚═╝');
        $this->info('');
        $this->info('  پکیج پیامک ایرانی برای Laravel');
        $this->line('');

        // ۱. انتشار config
        $this->info('▶ انتشار فایل تنظیمات...');
        $this->callSilent('vendor:publish', [
            '--tag'   => 'notifak-config',
            '--force' => false,
        ]);
        $this->line('  ✔ config/notifak.php ایجاد شد');

        // ۲. انتشار و اجرای migrations
        $this->info('▶ انتشار migrations...');
        $this->callSilent('vendor:publish', ['--tag' => 'notifak-migrations']);
        $this->line('  ✔ فایل‌های migration کپی شدند');

        if ($this->confirm('  آیا migration اجرا شود؟', true)) {
            $this->call('migrate');
        }

        // ۳. نمایش مراحل بعدی
        $this->line('');
        $this->info('✅ نصب با موفقیت انجام شد!');
        $this->line('');
        $this->warn('📌 مراحل بعدی:');
        $this->line('');
        $this->line('  ۱. تنظیمات را در فایل .env اضافه کنید:');
        $this->line('');
        $this->line('     NOTIFAK_DEFAULT_DRIVER=smsir');
        $this->line('     NOTIFAK_FALLBACK_DRIVER=ghasedak');
        $this->line('     SMSIR_API_KEY=your-api-key');
        $this->line('     SMSIR_SENDER=3000xxxxxx');
        $this->line('');
        $this->line('  ۲. ارسال پیامک آزمایشی:');
        $this->line('     php artisan notifak:test 09123456789');
        $this->line('');
        $this->line('  ۳. مستندات کامل: https://github.com/extenbox/notify');
        $this->line('');

        return self::SUCCESS;
    }
}
