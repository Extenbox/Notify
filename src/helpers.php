<?php

use Extenbox\Notify\NotifakManager;
use Extenbox\Notify\PendingSms;

if (!function_exists('notifak')) {
    /**
     * دسترسی سریع به Notifak
     *
     * notifak()                        → NotifakManager instance
     * notifak('09123456789', 'پیام')   → PendingSms (ارسال زنجیره‌ای)
     */
    function notifak(string|array|null $to = null, string|null $message = null): NotifakManager|PendingSms
    {
        $manager = app('notifak');

        if ($to !== null && $message !== null) {
            return $manager->send($to, $message);
        }

        return $manager;
    }
}
