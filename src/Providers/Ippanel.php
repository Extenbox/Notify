<?php

declare(strict_types=1);

namespace Extenbox\Notify\Providers;

use Extenbox\Notify\Contracts\SmsProviderInterface;
use Extenbox\Notify\Exceptions\NotifyException;
use Extenbox\Notify\Support\HttpClient;

class Ippanel implements SmsProviderInterface
{
    protected array $config = [];

    public function __construct(protected ?HttpClient $http = null)
    {
        $this->http ??= new HttpClient();
    }

    public function config(array $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function send(string|array $to, string $message, array $options = []): mixed
    {
        return $this->http->postJson($this->endpoint(), array_merge([
            'sending_type' => 'webservice',
            'from_number' => $this->sender(),
            'message' => $message,
            'recipients' => $this->recipients($to),
        ], $options), $this->headers());
    }

    public function pattern(string|array $to, string $pattern, array $params = [], array $options = []): mixed
    {
        return $this->http->postJson($this->endpoint(), array_merge([
            'sending_type' => 'pattern',
            'from_number' => $this->sender(),
            'code' => $pattern,
            'recipients' => $this->recipients($to),
            'params' => $params,
        ], $options), $this->headers());
    }

    protected function endpoint(): string
    {
        return rtrim($this->config['base_url'] ?? 'https://edge.ippanel.com/v1/api', '/') . '/send';
    }

    protected function headers(): array
    {
        $token = $this->config['token'] ?? $this->config['api_key'] ?? $this->config['api'] ?? null;

        if (! $token) {
            throw new NotifyException('IPPanel token/api_key is required.');
        }

        return ['Authorization' => $token];
    }

    protected function sender(): string
    {
        $sender = $this->config['sender'] ?? $this->config['from'] ?? null;

        if (! $sender) {
            throw new NotifyException('IPPanel sender/from number is required.');
        }

        return (string) $sender;
    }

    protected function recipients(string|array $to): array
    {
        return array_values((array) $to);
    }
}
