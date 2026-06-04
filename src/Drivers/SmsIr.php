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
        $response = $this->sendBulk($message, $this->normalizePhones($to));

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

        $response = $this->sendVerify($phones[0], (int) $patternCode, $variables);

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

    public function sendBulk(string $message, array $mobiles, ?int $sendAt = null, ?string $lineNumber = null): array
    {
        return $this->post('send/bulk', [
            'lineNumber'   => $lineNumber ?? $this->getSender(),
            'messageText'  => $message,
            'mobiles'      => $mobiles,
            'sendDateTime' => $sendAt,
        ]);
    }

    public function sendLikeToLike(array $messages, array $mobiles, ?int $sendAt = null, ?string $lineNumber = null): array
    {
        return $this->post('send/likeToLike', [
            'lineNumber'    => $lineNumber ?? $this->getSender(),
            'messageTexts'  => $messages,
            'mobiles'       => $mobiles,
            'sendDateTime'  => $sendAt,
        ]);
    }

    public function sendVerify(string $mobile, int $templateId, array $parameters): array
    {
        $params = [];
        foreach ($parameters as $key => $value) {
            $params[] = ['name' => (string) $key, 'value' => (string) $value];
        }

        return $this->post('send/verify', [
            'mobile'     => $mobile,
            'templateId' => $templateId,
            'parameters' => $params,
        ]);
    }

    public function deleteScheduled(string|int $packId): array
    {
        return $this->delete('send/scheduled/' . $packId);
    }

    public function getCredit(): array
    {
        return $this->get('credit');
    }

    public function getLineNumbers(): array
    {
        return $this->get('line');
    }

    public function getSentReport(array $filters = []): array
    {
        return $this->get('report/sent', $filters);
    }

    public function getReceivedReport(array $filters = []): array
    {
        return $this->get('report/received', $filters);
    }
}
