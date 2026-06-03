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

        $response = $this->post('/Send', array_merge($this->getAuthParams(), [
            'from' => $this->getSender(),
            'to'   => $phones,
            'text' => $message,
        ]));

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

        $response = $this->post('/BaseService.svc/Rest/SendByBaseNumber2', array_merge($this->getAuthParams(), [
            'text'   => $text,
            'to'     => $phones[0],
            'bodyId' => (int) $patternCode,
        ]));

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
}
