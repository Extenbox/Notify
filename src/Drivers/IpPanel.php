<?php

namespace Extenbox\Notify\Drivers;

use Extenbox\Notify\Contracts\SmsResponse;

/**
 * درایور IPPanel
 * https://edge.ippanel.com/v1
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
            'Authorization' => $this->config['authorization']
                ?? (($this->config['auth_prefix'] ?? '') . ($this->config['api_key'] ?? '')),
        ];
    }

    public function sendNormal(string|array $to, string $message): SmsResponse
    {
        $phones = $this->normalizePhones($to);

        $response = $this->sendOneToMany($this->getSender(), $phones, $message);

        if (isset($response['error'])) {
            return SmsResponse::failure($response['error']);
        }

        if ($this->isSuccessfulResponse($response)) {
            return SmsResponse::success($response['data'] ?? $response);
        }

        return SmsResponse::failure(
            $response['meta']['message'] ?? $response['message'] ?? 'خطای ناشناخته',
            $response['status'] ?? null,
            $response
        );
    }

    public function sendPattern(string|array $to, string $patternCode, array $variables): SmsResponse
    {
        $phones = $this->normalizePhones($to);

        $response = $this->sendPatternMessage($patternCode, $this->getSender(), $phones[0], $variables);

        if (isset($response['error'])) {
            return SmsResponse::failure($response['error']);
        }

        if ($this->isSuccessfulResponse($response)) {
            return SmsResponse::success($response['data'] ?? $response);
        }

        return SmsResponse::failure(
            $response['meta']['message'] ?? $response['message'] ?? 'خطای ناشناخته',
            $response['status'] ?? null,
            $response
        );
    }

    public function getCredit(): array
    {
        return $this->get('api//payment/credit/mine');
    }

    public function sendOneToMany(string $originator, array $recipients, string $message, ?string $summary = null, ?string $sendTime = null): array
    {
        return $this->post('api/send', $this->withoutNulls([
            'sending_type' => 'webservice',
            'from_number' => $this->formatNumber($originator),
            'message' => $message,
            'params' => ['recipients' => $this->formatNumbers($recipients)],
            'description' => $summary,
            'send_time' => $sendTime,
        ]));
    }

    public function sendPeerToPeer(array $items, ?string $sendTime = null): array
    {
        return $this->post('api/send', $this->withoutNulls([
            'sending_type' => 'peer_to_peer',
            'params' => $this->formatPeerToPeerItems($items),
            'send_time' => $sendTime,
        ]));
    }

    public function sendPatternMessage(string $patternCode, string $originator, string $recipient, array $values): array
    {
        return $this->post('api/send', $this->withoutNulls([
            'sending_type' => 'pattern',
            'from_number' => $this->formatNumber($originator),
            'code' => $patternCode,
            'recipients' => [$this->formatNumber($recipient)],
            'params' => $values,
        ]));
    }

    public function cancelScheduled(int|string $messageOutboxId): array
    {
        return $this->post('api/send/cancel', ['message_outbox_id' => $messageOutboxId]);
    }

    public function getMessage(int|string $messageId): array
    {
        return $this->get('api/report/by_bulk', ['messages_outbox_id' => $messageId]);
    }

    public function fetchStatuses(int|string $messageId, int $page = 1, int $limit = 10): array
    {
        return $this->post('api/report/messages', [
            'page' => $page,
            'limit' => $limit,
            'filters' => ['messages_outbox_id' => (string) $messageId],
        ]);
    }

    public function fetchInbox(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        return $this->post('api/report/messages-inbox', $this->withoutNulls([
            'page' => $page,
            'per_page' => $perPage,
            'filters' => $filters,
        ]));
    }

    public function createPattern(string $pattern, string $description, array $variables, string $delimiter = '%', bool $isShared = false): array
    {
        return $this->post('api/patterns', $this->withoutNulls([
            'message' => $pattern,
            'delimiter' => $delimiter,
            'description' => $description,
            'variable' => $variables,
            'is_share' => $isShared,
        ]));
    }

    protected function withoutNulls(array $payload): array
    {
        return array_filter($payload, fn($value) => $value !== null);
    }

    protected function isSuccessfulResponse(array $response): bool
    {
        return ($response['meta']['status'] ?? null) === true
            || ($response['status'] ?? null) === 0;
    }

    protected function formatNumbers(array $numbers): array
    {
        return array_map(fn($number) => $this->formatNumber((string) $number), $numbers);
    }

    protected function formatNumber(string $number): string
    {
        $number = trim($number);

        if (str_starts_with($number, '+')) {
            return $number;
        }

        $digits = preg_replace('/\D/', '', $number) ?? '';

        if (str_starts_with($digits, '00')) {
            return '+' . substr($digits, 2);
        }

        if (str_starts_with($digits, '98')) {
            return '+' . $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '+98' . substr($digits, 1);
        }

        return '+98' . $digits;
    }

    protected function formatPeerToPeerItems(array $items): array
    {
        return array_map(function (array $item): array {
            if (isset($item['recipients']) && is_array($item['recipients'])) {
                $item['recipients'] = $this->formatNumbers($item['recipients']);
            }

            return $item;
        }, $items);
    }
}
