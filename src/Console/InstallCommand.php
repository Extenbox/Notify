<?php

namespace Extenbox\Notify\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature   = 'notify:install';
    protected $aliases     = ['Notify:install'];
    protected $description = 'نصب و راه‌اندازی پکیج Notify';

    public function handle(): int
    {
        $this->info('');
        $this->info('  ███╗   ██╗ ██████╗ ████████╗██╗███████╗██╗   ██╗ ');
        $this->info('  ████╗  ██║██╔═══██╗╚══██╔══╝██║██╔════╗ ██  ██╔╝ ');
        $this->info('  ██╔██╗ ██║██║   ██║   ██║   ██║█████╗     ██     ');
        $this->info('  ██║╚██╗██║██║   ██║   ██║   ██║██╔══╝     ██╔    ');
        $this->info('  ██║ ╚████║╚██████╔╝   ██║   ██║██║        ██║    ');
        $this->info('  ╚═╝  ╚═══╝ ╚═════╝    ╚═╝   ╚═╝╚═╝        ╚═╝    ');
        $this->info('');
        $this->info('پکیج پیامک  ');
        $this->line('');

        // ۱. انتشار config
        $this->info('▶ انتشار فایل تنظیمات...');
        $this->callSilent('vendor:publish', [
            '--tag'   => 'Notify-config',
            '--force' => false,
        ]);
        $this->line('  ✔ config/Notify.php ایجاد شد');

        // ۲. انتشار و اجرای migrations
        $this->info('▶ انتشار migrations...');
        $this->callSilent('vendor:publish', ['--tag' => 'Notify-migrations']);
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
        $this->line('     Notify_DEFAULT_DRIVER=smsir');
        $this->line('     Notify_FALLBACK_DRIVER=ghasedak');
        $this->line('     SMSIR_API_KEY=your-api-key');
        $this->line('     SMSIR_SENDER=3000xxxxxx');
        $this->line('');
        $this->line('  ۲. ارسال پیامک آزمایشی:');
        $this->line('     php artisan notify:test 09123456789');
        $this->line('');
        $this->line('  ۳. مستندات کامل: https://github.com/extenbox/notify');
        $this->line('');

        return self::SUCCESS;
    }
}
