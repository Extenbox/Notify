<?php

namespace Extenbox\Notify\Commands;

use Illuminate\Console\Command;
use Extenbox\Notify\Facades\Notify;

class SendTestSmsCommand extends Command
{
    protected $signature = 'notify:send-test
                            {phone : شماره موبایل گیرنده}
                            {--driver= : نام پنل (پیش‌فرض: تنظیمات config)}
                            {--sender= : شماره ارسال}
                            {--message=این یک پیامک آزمایشی از Notify است : متن پیام}';

    protected $description = 'ارسال پیامک آزمایشی برای تست تنظیمات Notify';

    public function handle(): int
    {
        $phone   = $this->argument('phone');
        $message = $this->option('message');
        $driver  = $this->option('driver');
        $sender  = $this->option('sender');

        $this->info("📱 در حال ارسال پیامک آزمایشی به: {$phone}");

        $pending = Notify::message($phone, $message);

        if ($driver) {
            $pending->via($driver, $sender ?: null);
        }

        $response = $pending->send();

        if ($response->isSuccessful()) {
            $this->info('✅ پیامک با موفقیت ارسال شد!');
            $this->line('پاسخ: ' . json_encode($response->data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        $this->error('❌ خطا در ارسال: ' . $response->message);
        $this->line('پاسخ: ' . json_encode($response->data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return self::FAILURE;
    }
}
