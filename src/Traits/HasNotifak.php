<?php

namespace Extenbox\Notify\Traits;

use Extenbox\Notify\PendingSms;
use Extenbox\Notify\Facades\Notifak;

/**
 * Trait HasNotifak
 *
 * این trait را به model کاربران خود اضافه کنید تا بتوانید
 * مستقیماً به آن پیامک بفرستید.
 *
 * استفاده:
 *   class User extends Model {
 *       use HasNotifak;
 *       public function routeNotificationForNotifak(): string { return $this->mobile; }
 *   }
 *
 *   $user->sendSms('کد تأیید: 12345');
 *   $user->sendSms('کد تأیید: 12345')->via('smsir');
 */
trait HasNotifak
{
    /**
     * شماره موبایل مدل برای ارسال پیامک
     * این متد را در model خود override کنید
     */
    public function routeNotificationForNotifak(): string
    {
        return $this->mobile
            ?? $this->phone
            ?? $this->phone_number
            ?? '';
    }

    /**
     * ارسال پیامک به این کاربر
     */
    public function sendSms(string $message): PendingSms
    {
        $phone = $this->routeNotificationForNotifak();

        if (empty($phone)) {
            throw new \RuntimeException(
                'شماره موبایل تعریف نشده. متد routeNotificationForNotifak را پیاده‌سازی کنید.'
            );
        }

        return Notifak::send($phone, $message);
    }
}
