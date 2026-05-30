<?php

declare(strict_types=1);

namespace Extenbox\Notify\Providers;

use Extenbox\Notify\Contracts\SmsProviderInterface;
use Extenbox\Notify\Exceptions\NotifyException;
use Extenbox\Notify\Support\HttpClient;

class Ghasedak implements SmsProviderInterface
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
        return $this->http->postForm($this->endpoint('/sms/send/simple'), array_merge([
            'message' => $message,
            'receptor' => implode(',', (array) $to),
            'linenumber' => $this->sender(),
        ], $options), $this->headers());
    }

    public function pattern(string|array $to, string $pattern, array $params = [], array $options = []): mixed
    {
        return $this->http->postForm($this->endpoint('/sms/verify'), array_merge([
            'receptor' => is_array($to) ? reset($to) : $to,
            'type' => $options['type'] ?? 1,
            'template' => $pattern,
        ], $this->formatPatternParams($params), $options), $this->headers());
    }

    protected function endpoint(string $path): string
    {
        return rtrim($this->config['base_url'] ?? 'https://api.ghasedak.me/v2', '/') . $path;
    }

    protected function headers(): array
    {
        $apiKey = $this->config['api_key'] ?? $this->config['api'] ?? null;

        if (! $apiKey) {
            throw new NotifyException('Ghasedak api_key is required.');
        }

        return ['apikey' => $apiKey];
    }

    protected function sender(): string
    {
        return (string) ($this->config['sender'] ?? $this->config['linenumber'] ?? '');
    }

    protected function formatPatternParams(array $params): array
    {
        if (! array_is_list($params)) {
            $params = array_values($params);
        }

        $formatted = [];

        foreach (array_slice($params, 0, 10) as $index => $value) {
            $formatted['param' . ($index + 1)] = $value;
        }

        return $formatted;
    }
}
