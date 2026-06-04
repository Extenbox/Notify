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

        $result = $this->sendSimple(implode(',', $phones), $message, $this->getSender());

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

        $result = $this->postForm('verification/send/simple', $params, $this->defaultHeaders());

        if (isset($result['result']['code']) && $result['result']['code'] === 200) {
            return SmsResponse::success($result);
        }

        return SmsResponse::failure(
            $result['result']['message'] ?? 'خطای ناشناخته',
            $result['result']['code'] ?? null,
            $result
        );
    }

    public function sendSimple(string $receptor, string $message, ?string $lineNumber = null, ?int $sendDate = null, ?string $checkId = null): array
    {
        return $this->postForm('sms/send/simple', [
            'receptor'   => $receptor,
            'linenumber' => $lineNumber,
            'message'    => $message,
            'senddate'   => $sendDate,
            'checkid'    => $checkId,
        ], $this->defaultHeaders());
    }

    public function sendBulk(string|array $lineNumber, string|array $receptor, string|array $message, string|array|null $date = null, ?string $checkId = null): array
    {
        return $this->postForm('sms/send/bulk', [
            'receptor'   => $this->csv($receptor),
            'linenumber' => $this->csv($lineNumber),
            'message'    => $this->csv($message),
            'senddate'   => $this->csv($date),
            'checkid'    => $checkId,
        ], $this->defaultHeaders());
    }

    public function sendPair(string $lineNumber, string|array $receptor, string $message, ?int $date = null, ?string $checkId = null): array
    {
        return $this->postForm('sms/send/pair', [
            'receptor'   => $this->csv($receptor),
            'linenumber' => $lineNumber,
            'message'    => $message,
            'senddate'   => $date,
            'checkid'    => $checkId,
        ], $this->defaultHeaders());
    }

    public function status(string|array $ids, int $type = 1): array
    {
        return $this->get('sms/status', [
            'id' => $this->csv($ids),
            'type' => $type,
        ], $this->defaultHeaders());
    }

    public function addGroup(string $name, ?int $parent = null): array
    {
        return $this->postForm('contact/group/new', compact('name', 'parent'), $this->defaultHeaders());
    }

    public function addNumber(int $groupId, string|array $number, string|array|null $firstname = null, string|array|null $lastname = null, string|array|null $email = null): array
    {
        return $this->postForm('contact/group/addnumber', [
            'groupid' => $groupId,
            'number' => $this->csv($number),
            'firstname' => $this->csv($firstname),
            'lastname' => $this->csv($lastname),
            'email' => $this->csv($email),
        ], $this->defaultHeaders());
    }

    public function groupList(?int $parent = null): array
    {
        return $this->get('contact/group/list', ['parent' => $parent], $this->defaultHeaders());
    }

    public function groupNumberList(int $groupId, ?int $offset = null, ?int $page = null): array
    {
        return $this->get('contact/group/listnumber', ['groupid' => $groupId, 'offset' => $offset, 'page' => $page], $this->defaultHeaders());
    }

    public function groupEdit(int $groupId, string $name): array
    {
        return $this->postForm('contact/group/edit', ['groupid' => $groupId, 'name' => $name], $this->defaultHeaders());
    }

    public function groupRemove(int $groupId): array
    {
        return $this->postForm('contact/group/remove', ['groupid' => $groupId], $this->defaultHeaders());
    }

    public function receiveSms(string $lineNumber, bool|int $isRead): array
    {
        return $this->postForm('sms/receive/last', ['linenumber' => $lineNumber, 'isread' => (int) $isRead], $this->defaultHeaders());
    }

    public function receivePaging(string $lineNumber, bool|int $isRead, int $fromDate, int $toDate, int $page, int $offset): array
    {
        return $this->postForm('sms/receive/paging', [
            'linenumber' => $lineNumber,
            'isread' => (int) $isRead,
            'fromdate' => $fromDate,
            'todate' => $toDate,
            'page' => $page,
            'offset' => $offset,
        ], $this->defaultHeaders());
    }

    public function cancelSms(string|array $messageId): array
    {
        return $this->postForm('sms/cancel', ['messageid' => $this->csv($messageId)], $this->defaultHeaders());
    }

    public function accountInfo(): array
    {
        return $this->get('account/info', [], $this->defaultHeaders());
    }

    protected function csv(mixed $value): mixed
    {
        return is_array($value) ? implode(',', $value) : $value;
    }
}
