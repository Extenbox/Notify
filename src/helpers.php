<?php

use Extenbox\Notify\NotifyManager;
use Extenbox\Notify\Notify as NotifyGateway;
use Extenbox\Notify\PendingSms;

if (!function_exists('Notify')) {
    /**
     * دسترسی سریع به Notify
     *
     * Notify()                        → NotifyManager instance
     * Notify('09123456789', 'پیام')   → PendingSms (ارسال زنجیره‌ای)
     */
    function Notify(string|array|null $to = null, string|null $message = null): NotifyManager|PendingSms
    {
        try {
            $manager = function_exists('app') ? app('Notify') : NotifyGateway::manager();
        } catch (\Throwable) {
            $manager = NotifyGateway::manager();
        }

        if ($to !== null && $message !== null) {
            return $manager->send($to, $message);
        }

        return $manager;
    }
}
