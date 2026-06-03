<?php

namespace Extenbox\Notify\Drivers;

use Extenbox\Notify\Contracts\SmsResponse;

/**
 * درایور مدیانا
 * https://rest.mediana.ir
 */
class Mediana extends BaseDriver
{
    public function getName(): string
    {
        return 'mediana';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'ApiKey'       => $this->config['api_key'] ?? '',
        ];
    }

    public function sendNormal(string|array $to, string $message): SmsResponse
    {
        $phones = $this->normalizePhones($to);

        $response = $this->post('/Message', [
            'Content'    => $message,
            'MobileList' => $phones,
            'SendDate'   => null,
            'SenderList' => [$this->getSender()],
        ]);

        if (isset($response['error'])) {
            return SmsResponse::failure($response['error']);
        }

        if (isset($response['StatusCode']) && $response['StatusCode'] === 0) {
            return SmsResponse::success($response);
        }

        return SmsResponse::failure(
            $response['Message'] ?? 'خطای ناشناخته',
            $response['StatusCode'] ?? null,
            $response
        );
    }

    public function sendPattern(string|array $to, string $patternCode, array $variables): SmsResponse
    {
        $phones = $this->normalizePhones($to);

        $params = [];
        foreach ($variables as $key => $value) {
            $params[] = ['Parameter' => $key, 'ParameterValue' => (string) $value];
        }

        $response = $this->post('/Message/Pattern', [
            'PatternCode'  => $patternCode,
            'SenderNumber' => $this->getSender(),
            'Mobile'       => $phones[0], // مدیانا یک شماره در pattern
            'Parameters'   => $params,
        ]);

        if (isset($response['error'])) {
            return SmsResponse::failure($response['error']);
        }

        if (isset($response['StatusCode']) && $response['StatusCode'] === 0) {
            return SmsResponse::success($response);
        }

        return SmsResponse::failure(
            $response['Message'] ?? 'خطای ناشناخته',
            $response['StatusCode'] ?? null,
            $response
        );
    }
}
