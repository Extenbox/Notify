<?php

namespace Extenbox\Notify\Channels;

use Illuminate\Notifications\Notification;
use Extenbox\Notify\Contracts\SmsResponse;
use Extenbox\Notify\Facades\Notifak;

/**
 * کانال پیامک برای سیستم Notification لاراول
 *
 * استفاده در Notification:
 *
 *   public function via($notifiable): array
 *   {
 *       return [NotifakChannel::class];
 *   }
 *
 *   public function toNotifak($notifiable): NotifakMessage
 *   {
 *       return (new NotifakMessage)
 *           ->content('کد تأیید: 12345')
 *           ->via('smsir')
 *           ->sender('30007732...');
 *   }
 */
class NotifakChannel
{
    public function send(mixed $notifiable, Notification $notification): ?SmsResponse
    {
        if (!method_exists($notification, 'toNotifak')) {
            return null;
        }

        $message = $notification->toNotifak($notifiable);

        if (!($message instanceof NotifakMessage)) {
            return null;
        }

        $phone = $message->getPhone()
            ?? $notifiable->routeNotificationFor('notifak')
            ?? $notifiable->routeNotificationFor('sms')
            ?? $notifiable->phone
            ?? $notifiable->mobile
            ?? null;

        if (!$phone) {
            return null;
        }

        $pending = Notifak::send($phone, $message->getContent());

        if ($message->getProvider()) {
            $pending->via($message->getProvider(), $message->getSender());
        }

        if ($message->getType() === 'pattern') {
            $pending->type('pattern', $message->getPatternCode(), $message->getVariables());
        }

        return $pending->send();
    }
}
