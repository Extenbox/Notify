<?php

namespace Extenbox\Notify\Channels;

use Illuminate\Notifications\Notification;
use Extenbox\Notify\Contracts\SmsResponse;
use Extenbox\Notify\Facades\Notify;

/**
 * کانال پیامک برای سیستم Notification لاراول
 *
 * استفاده در Notification:
 *
 *   public function via($notifiable): array
 *   {
 *       return [NotifyChannel::class];
 *   }
 *
 *   public function toNotify($notifiable): NotifyMessage
 *   {
 *       return (new NotifyMessage)
 *           ->content('کد تأیید: 12345')
 *           ->via('smsir')
 *           ->sender('30007732...');
 *   }
 */
class NotifyChannel
{
    public function send(mixed $notifiable, Notification $notification): ?SmsResponse
    {
        if (!method_exists($notification, 'toNotify')) {
            return null;
        }

        $message = $notification->toNotify($notifiable);

        if (!($message instanceof NotifyMessage)) {
            return null;
        }

        $phone = $message->getPhone()
            ?? $notifiable->routeNotificationFor('Notify')
            ?? $notifiable->routeNotificationFor('sms')
            ?? $notifiable->phone
            ?? $notifiable->mobile
            ?? null;

        if (!$phone) {
            return null;
        }

        if ($message->getPatternCode() !== null) {
            $pending = Notify::flash(
                $phone,
                $message->getPatternCode(),
                $message->getVariables()
            );
        } else {
            $pending = Notify::sms($phone, $message->getContent());
        }

        if ($message->getProvider()) {
            $pending->via($message->getProvider(), $message->getSender());
        }

        return $pending->send();
    }
}
