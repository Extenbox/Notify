<?php

declare(strict_types=1);

namespace Extenbox\Notify\Providers;

use Extenbox\Notify\Contracts\SmsProviderInterface;
use Extenbox\Notify\Exceptions\NotifyException;
use Extenbox\Notify\Support\HttpClient;

class Melipayamak implements SmsProviderInterface
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
        return $this->http->postForm($this->sendEndpoint(), array_merge([
            'username' => $this->username(),
            'password' => $this->password(),
            'from' => $this->sender(),
            'to' => implode(',', (array) $to),
            'text' => $message,
            'isflash' => false,
        ], $options));
    }

    public function pattern(string|array $to, string $pattern, array $params = [], array $options = []): mixed
    {
        return $this->http->postForm($this->patternEndpoint(), array_merge([
            'username' => $this->username(),
            'password' => $this->password(),
            'text' => $this->formatPatternText($params),
            'to' => is_array($to) ? reset($to) : $to,
            'bodyId' => $pattern,
        ], $options));
    }

    protected function sendEndpoint(): string
    {
        return $this->config['send_url'] ?? 'https://rest.payamak-panel.com/api/SendSMS/SendSMS';
    }

    protected function patternEndpoint(): string
    {
        return $this->config['pattern_url'] ?? 'https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber';
    }

    protected function username(): string
    {
        return $this->required('username');
    }

    protected function password(): string
    {
        return $this->required('password');
    }

    protected function sender(): string
    {
        return $this->required('sender', 'from');
    }

    protected function required(string ...$keys): string
    {
        foreach ($keys as $key) {
            if (isset($this->config[$key]) && $this->config[$key] !== '') {
                return (string) $this->config[$key];
            }
        }

        throw new NotifyException(sprintf('Melipayamak config [%s] is required.', implode(' or ', $keys)));
    }

    protected function formatPatternText(array $params): string
    {
        if (array_is_list($params)) {
            return implode(';', $params);
        }

        return implode(';', array_values($params));
    }
}
