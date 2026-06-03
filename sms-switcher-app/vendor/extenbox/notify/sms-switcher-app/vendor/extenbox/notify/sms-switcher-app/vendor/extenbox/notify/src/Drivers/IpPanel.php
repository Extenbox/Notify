<?php

namespace Extenbox\Notify\Drivers;

use Extenbox\Notify\Contracts\SmsResponse;

/**
 * درایور IPPanel
 * https://edge.ippanel.com/v1public
 */
class IpPanel extends BaseDriver
{
    public function getName(): string
    {
        return 'ippanel';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => 'AccessKey ' . ($this->config['api_key'] ?? ''),
        ];
    }

    public function sendNormal(string|array $to, string $message): SmsResponse
    {
        $phones = $this->normalizePhones($to);

        $response = $this->post('/messages/send', [
            'originator' => $this->getSender(),
            'recipients' => $phones,
            'message'    => $message,
        ]);

        if (isset($response['error'])) {
            return SmsResponse::failure($response['error']);
        }

        // IPPanel: status 0 = موفق
        if (isset($response['status']) && $response['status'] === 0) {
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

        $response = $this->post('/messages/pattern/send', [
            'patternCode' => $patternCode,
            'originator'  => $this->getSender(),
            'recipient'   => $phones[0],
            'values'      => $variables,
        ]);

        if (isset($response['error'])) {
            return SmsResponse::failure($response['error']);
        }

        if (isset($response['status']) && $response['status'] === 0) {
            return SmsResponse::success($response['data'] ?? $response);
        }

        return SmsResponse::failure(
            $response['message'] ?? 'خطای ناشناخته',
            $response['status'] ?? null,
            $response
        );
    }
}
