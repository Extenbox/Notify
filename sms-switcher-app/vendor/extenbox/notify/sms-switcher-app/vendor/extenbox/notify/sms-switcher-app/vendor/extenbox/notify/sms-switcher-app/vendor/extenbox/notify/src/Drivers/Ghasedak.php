<?php

namespace Extenbox\Notify\Drivers;

use Extenbox\Notify\Contracts\SmsResponse;

/**
 * درایور قاصدک
 * https://api.ghasedak.me/v2
 */
class Ghasedak extends BaseDriver
{
    public function getName(): string
    {
        return 'ghasedak';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'apikey'       => $this->config['api_key'] ?? '',
        ];
    }

    public function sendNormal(string|array $to, string $message): SmsResponse
    {
        $phones = $this->normalizePhones($to);

        try {
            $response = $this->client->post('/sms/send/simple', [
                'headers'     => $this->defaultHeaders(),
                'form_params' => [
                    'receptor'  => implode(',', $phones),
                    'linenumber' => $this->getSender(),
                    'message'   => $message,
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (\Exception $e) {
            return SmsResponse::failure($e->getMessage());
        }

        if (isset($result['result']['code']) && $result['result']['code'] === 200) {
            return SmsResponse::success($result);
        }

        return SmsResponse::failure(
            $result['result']['message'] ?? 'خطای ناشناخته',
            $result['result']['code'] ?? null,
            $result
        );
    }

    public function sendPattern(string|array $to, string $patternCode, array $variables): SmsResponse
    {
        $phones = $this->normalizePhones($to);

        $params = [
            'receptor'    => implode(',', $phones),
            'type'        => 1,
            'template'    => $patternCode,
        ];

        // قاصدک از param1, param2, ... استفاده می‌کند
        $i = 1;
        foreach ($variables as $value) {
            $params["param{$i}"] = (string) $value;
            $i++;
        }

        try {
            $response = $this->client->post('/verification/send/simple', [
                'headers'     => $this->defaultHeaders(),
                'form_params' => $params,
            ]);

            $result = json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (\Exception $e) {
            return SmsResponse::failure($e->getMessage());
        }

        if (isset($result['result']['code']) && $result['result']['code'] === 200) {
            return SmsResponse::success($result);
        }

        return SmsResponse::failure(
            $result['result']['message'] ?? 'خطای ناشناخته',
            $result['result']['code'] ?? null,
            $result
        );
    }
}
