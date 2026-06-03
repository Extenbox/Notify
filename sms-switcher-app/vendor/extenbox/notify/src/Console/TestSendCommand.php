<?php

namespace Extenbox\Notify\Console;

use Illuminate\Console\Command;
use Extenbox\Notify\NotifyManager;

class TestSendCommand extends Command
{
    protected $signature = 'Notify:test
                            {phone : شماره موبایل گیرنده}
                            {--driver= : نام درایور (پیش‌فرض: مقدار config)}
                            {--sender= : شماره ارسال}
                            {--message=پیامک آزمایشی از Notify : متن پیام}';

    protected $description = 'ارسال پیامک آزمایشی برای تست تنظیمات';

    public function handle(NotifyManager $Notify): int
    {
        $phone   = $this->argument('phone');
        $driver  = $this->option('driver') ?? $Notify->getDefaultDriver();
        $sender  = $this->option('sender');
        $message = $this->option('message');

        $this->info("▶ ارسال پیامک آزمایشی...");
        $this->table(
            ['فیلد', 'مقدار'],
            [
                ['گیرنده', $phone],
                ['درایور', $driver],
                ['شماره ارسال', $sender ?? '(پیش‌فرض)'],
                ['متن', $message],
            ]
        );

        $pending = $Notify->send($phone, $message);

        if ($driver) {
            $pending->via($driver, $sender);
        }

        $response = $pending->send();

        if ($response->isSuccessful()) {
            $this->info('');
            $this->info('✅ پیامک با موفقیت ارسال شد!');
            $this->line('   پیام: ' . $response->message);
        } else {
            $this->error('');
            $this->error('❌ خطا در ارسال پیامک');
            $this->line('   پیام خطا: ' . $response->message);
            $this->line('   کد وضعیت: ' . ($response->statusCode ?? 'N/A'));
        }

        return $response->isSuccessful() ? self::SUCCESS : self::FAILURE;
    }
}
