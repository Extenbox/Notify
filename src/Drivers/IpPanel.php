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
        return $this->get('api/packages/credit');
    }

    public function sendOneToMany(string $originator, array $recipients, string $message, ?string $summary = null, ?string $sendTime = null): array
    {
        return $this->post('api/send', $this->withoutNulls([
            'sending_type' => 'webservice',
            'from_number' => $originator,
            'message' => $message,
            'params' => ['recipients' => $recipients],
            'description' => $summary,
            'send_time' => $sendTime,
        ]));
    }

    public function sendPeerToPeer(array $items, ?string $sendTime = null): array
    {
        return $this->post('api/send', $this->withoutNulls([
            'sending_type' => 'peer_to_peer',
            'params' => ['items' => $items],
            'send_time' => $sendTime,
        ]));
    }

    public function sendPatternMessage(string $patternCode, string $originator, string $recipient, array $values): array
    {
        return $this->post('api/send', $this->withoutNulls([
            'sending_type' => 'pattern',
            'from_number' => $originator,
            'code' => $patternCode,
            'params' => [
                'recipient' => $recipient,
                'variable' => $values,
            ],
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
}
