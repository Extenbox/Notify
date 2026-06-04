<?php

namespace Extenbox\Notify\Drivers;

use Extenbox\Notify\Contracts\SmsResponse;

/**
 * درایور ملی پیامک
 * https://rest.payamak-panel.com
 */
class MeliPayamak extends BaseDriver
{
    public function getName(): string
    {
        return 'melipayamak';
    }

    private function getAuthParams(): array
    {
        return [
            'username' => $this->config['username'] ?? '',
            'password' => $this->config['password'] ?? '',
        ];
    }

    public function sendNormal(string|array $to, string $message): SmsResponse
    {
        $phones = $this->normalizePhones($to);

        $response = $this->send($phones, $this->getSender(), $message);

        if (isset($response['error'])) {
            return SmsResponse::failure($response['error']);
        }

        // ملی پیامک: Value = شناسه پیام یا کد خطا (منفی = خطا)
        $value = $response['Value'] ?? null;
        if ($value && (int) $value > 0) {
            return SmsResponse::success($response);
        }

        return SmsResponse::failure(
            $this->interpretErrorCode((int)($value ?? 0)),
            (int)($value ?? 0),
            $response
        );
    }

    public function sendPattern(string|array $to, string $patternCode, array $variables): SmsResponse
    {
        $phones = $this->normalizePhones($to);

        // ملی پیامک از فرمت خاصی برای متغیرها استفاده می‌کند
        $text = $variables['text'] ?? implode(' ', $variables);

        $response = $this->sendByBaseNumber($text, $phones[0], (int) $patternCode);

        if (isset($response['error'])) {
            return SmsResponse::failure($response['error']);
        }

        $value = $response['Value'] ?? null;
        if ($value && (int) $value > 0) {
            return SmsResponse::success($response);
        }

        return SmsResponse::failure(
            $this->interpretErrorCode((int)($value ?? 0)),
            (int)($value ?? 0),
            $response
        );
    }

    private function interpretErrorCode(int $code): string
    {
        return match ($code) {
            -1  => 'اطلاعات کاربری صحیح نیست',
            -2  => 'موجودی کافی نیست',
            -3  => 'خط موردنظر یافت نشد',
            -4  => 'خطای سرور',
            -5  => 'تعداد شماره بیش از حد مجاز است',
            -6  => 'پیام حاوی کلمه غیرمجاز است',
            -7  => 'وضعیت پیام صحیح نیست',
            -8  => 'خط ارسال غیرفعال است',
            default => "خطای ناشناخته: $code",
        };
    }

    public function send(array|string $to, string $from, string $text, bool $isFlash = false): array
    {
        return $this->post('Send', array_merge($this->getAuthParams(), [
            'from' => $from,
            'to' => is_array($to) ? $to : [$to],
            'text' => $text,
            'isFlash' => $isFlash,
        ]));
    }

    public function sendByBaseNumber(string $text, string $to, int $bodyId): array
    {
        return $this->post('BaseService.svc/Rest/SendByBaseNumber2', array_merge($this->getAuthParams(), [
            'text' => $text,
            'to' => $to,
            'bodyId' => $bodyId,
        ]));
    }

    public function getCredit(): array
    {
        return $this->post('GetCredit', $this->getAuthParams());
    }

    public function getBasePrice(): array
    {
        return $this->post('GetBasePrice', $this->getAuthParams());
    }

    public function getNumbers(): array
    {
        return $this->post('GetNumbers', $this->getAuthParams());
    }

    public function getMessages(int|string $location, int $index, int $count, string $from = ''): array
    {
        return $this->post('GetMessages', array_merge($this->getAuthParams(), compact('location', 'index', 'count', 'from')));
    }

    public function isDelivered(int|string $recId): array
    {
        return $this->post('GetDeliveries2', array_merge($this->getAuthParams(), ['recId' => $recId]));
    }
}
