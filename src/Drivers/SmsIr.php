<?php

namespace Extenbox\Notify\Drivers;

use Extenbox\Notify\Contracts\SmsResponse;

/**
 * درایور SMS.ir
 * https://api.sms.ir/v1
 */
class SmsIr extends BaseDriver
{
    public function getName(): string
    {
        return 'smsir';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'x-api-key'    => $this->config['api_key'] ?? '',
        ];
    }

    public function sendNormal(string|array $to, string $message): SmsResponse
    {
        $phones = $this->normalizePhones($to);

        $messages = array_map(fn($phone) => [
            'mobile'  => $phone,
            'message' => $message,
        ], $phones);

        $response = $this->post('/send/bulk', [
            'lineNumber' => $this->getSender(),
            'messages'   => $messages,
            'sendDateTime' => null,
        ]);

        if (isset($response['error'])) {
            return SmsResponse::failure($response['error']);
        }

        if (isset($response['status']) && $response['status'] === 1) {
            return SmsResponse::success($response['data'] ?? $response);
        }

        return SmsResponse::failure(
            $response['message'] ?? 'خطای ناشناخته',
            $response['status'] ?? null,
            $response
        );
    }

    public function sendPattern(string|array $to, string $patternCode, array $variables): SmsResponse
    {
        $phones = $this->normalizePhones($to);

        $params = [];
        foreach ($variables as $key => $value) {
            $params[] = ['name' => $key, 'value' => (string) $value];
        }

        $response = $this->post('/send/verify', [
            'mobile'       => $phones[0],
            'templateId'   => (int) $patternCode,
            'parameters'   => $params,
        ]);

        if (isset($response['error'])) {
            return SmsResponse::failure($response['error']);
        }

        if (isset($response['status']) && $response['status'] === 1) {
            return SmsResponse::success($response['data'] ?? $response);
        }

        return SmsResponse::failure(
            $response['message'] ?? 'خطای ناشناخته',
            $response['status'] ?? null,
            $response
        );
    }
}
