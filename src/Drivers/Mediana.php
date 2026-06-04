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
            'X-API-KEY'    => $this->config['api_key'] ?? '',
        ];
    }

    public function sendNormal(string|array $to, string $message): SmsResponse
    {
        $response = $this->sendSms($this->normalizePhones($to), $message, $this->getSender());

        if (isset($response['error'])) {
            return SmsResponse::failure($response['error']);
        }

        if ($this->isSuccessfulResponse($response)) {
            return SmsResponse::success($response);
        }

        return SmsResponse::failure(
            $this->responseMessage($response),
            $this->responseStatusCode($response),
            $response
        );
    }

    public function sendPattern(string|array $to, string $patternCode, array $variables): SmsResponse
    {
        $phones = $this->normalizePhones($to);

        $response = $this->sendPatternSms($phones, $patternCode, $variables, $this->getSender());

        if (isset($response['error'])) {
            return SmsResponse::failure($response['error']);
        }

        if ($this->isSuccessfulResponse($response)) {
            return SmsResponse::success($response);
        }

        return SmsResponse::failure(
            $this->responseMessage($response),
            $this->responseStatusCode($response),
            $response
        );
    }

    public function sendSms(array $recipients, string $message, ?string $sendingNumber = null, ?string $type = null): array
    {
        $payload = [
            'recipients' => $recipients,
            'messageText' => $message,
        ];

        if ($sendingNumber) {
            $payload['sendingNumber'] = $sendingNumber;
        } elseif ($type) {
            $payload['type'] = $type;
        }

        return $this->post('sms/v1/send/sms', $payload);
    }

    public function sendPatternSms(array $recipients, string $patternCode, array $parameters, ?string $sendingNumber = null, ?string $type = null): array
    {
        $payload = [
            'recipients' => $recipients,
            'patternCode' => $patternCode,
            'parameters' => $parameters,
        ];

        if ($sendingNumber) {
            $payload['sendingNumber'] = $sendingNumber;
        } elseif ($type) {
            $payload['type'] = $type;
        }

        return $this->post('sms/v1/send/pattern', $payload);
    }

    public function sendOtp(string $recipient, string $patternCode, string $otpCode): array
    {
        return $this->post('sms/v1/send/otp', compact('patternCode', 'recipient', 'otpCode'));
    }

    public function sendArray(array $requests, ?string $type = null): array
    {
        return $this->post('sms/v1/send/array', [
            'Requests' => $requests,
            'Type' => $type,
        ]);
    }

    public function getBalance(): array
    {
        return $this->get('sms/v1/account/balance');
    }

    public function getLines(): array
    {
        return $this->get('sms/v1/account/lines');
    }

    public function getStatuses(array $messageCodes): array
    {
        return $this->get('sms/v1/status', ['messageCodes' => $messageCodes]);
    }

    public function getInbox(array $filters = []): array
    {
        return $this->get('sms/v1/send-requests/inbox', $filters);
    }

    public function getPattern(string $patternCode): array
    {
        return $this->get('sms/v1/get/pattern/' . rawurlencode($patternCode));
    }

    public function getPatternByTitle(string $patternTitle): array
    {
        return $this->get('sms/v1/get/pattern-title/' . rawurlencode($patternTitle));
    }

    protected function isSuccessfulResponse(array $response): bool
    {
        return ($response['data']['succeed'] ?? null) === true
            || ($response['meta']['code'] ?? null) === 'OK'
            || ($response['StatusCode'] ?? null) === 0;
    }

    protected function responseMessage(array $response): string
    {
        return $response['meta']['errorMessage']
            ?? $response['data']['message']
            ?? $response['Message']
            ?? 'خطای ناشناخته';
    }

    protected function responseStatusCode(array $response): ?int
    {
        $status = $response['data']['statusInt']
            ?? $response['StatusCode']
            ?? null;

        return is_numeric($status) ? (int) $status : null;
    }
}
