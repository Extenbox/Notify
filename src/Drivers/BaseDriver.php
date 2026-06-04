<?php

namespace Extenbox\Notify\Drivers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Extenbox\Notify\Contracts\SmsDriver;
use Extenbox\Notify\Contracts\SmsResponse;

abstract class BaseDriver implements SmsDriver
{
    protected Client $client;
    protected array  $config;
    protected string $sender;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->sender = $config['sender'] ?? '';
        $this->client = $this->makeClient();
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function setSender(string $sender): static
    {
        $this->sender = $sender;
        return $this;
    }

    public function setConfig(array $config): static
    {
        $this->config = array_merge($this->config, $config);
        if (isset($config['sender'])) {
            $this->sender = $config['sender'];
        }

        $this->client = $this->makeClient();

        return $this;
    }

    protected function makeClient(): Client
    {
        return new Client([
            'base_uri' => $this->normalizeBaseUri($this->config['base_url'] ?? ''),
            'timeout'  => $this->config['timeout'] ?? 30,
            'headers'  => $this->defaultHeaders(),
            'verify'   => $this->sslVerify(),
        ]);
    }

    protected function normalizeBaseUri(string $baseUrl): string
    {
        if ($baseUrl === '') {
            return '';
        }

        return rtrim($baseUrl, '/') . '/';
    }

    protected function normalizeUri(string $uri): string
    {
        return ltrim($uri, '/');
    }

    protected function sslVerify(): bool|string
    {
        $value = $this->config['ssl_verify']
            ?? $this->config['sslverify']
            ?? true;

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            'false', '0', 'no', 'off' => false,
            'true', '1', 'yes', 'on' => true,
            default => $value,
        };
    }

    /**
     * Normalize phone number to Iranian format
     */
    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '98' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '98')) {
            $phone = '98' . $phone;
        }

        return $phone;
    }

    protected function normalizePhones(string|array $to): array
    {
        $phones = is_array($to) ? $to : [$to];
        return array_map(fn($p) => $this->normalizePhone($p), $phones);
    }

    /**
     * Helper: POST request with error handling
     */
    protected function post(string $uri, array $data = [], array $headers = []): array
    {
        $options = ['json' => $data];
        if (!empty($headers)) {
            $options['headers'] = $headers;
        }

        return $this->request('POST', $uri, $options);
    }

    /**
     * Helper: GET request
     */
    protected function get(string $uri, array $query = [], array $headers = []): array
    {
        $options = ['query' => $query];
        if (!empty($headers)) {
            $options['headers'] = $headers;
        }

        return $this->request('GET', $uri, $options);
    }

    protected function delete(string $uri, array $data = [], array $headers = []): array
    {
        $options = ['json' => $data];
        if (!empty($headers)) {
            $options['headers'] = $headers;
        }

        return $this->request('DELETE', $uri, $options);
    }

    protected function postForm(string $uri, array $data = [], array $headers = []): array
    {
        $options = ['form_params' => $data];
        if (!empty($headers)) {
            $options['headers'] = $headers;
        }

        return $this->request('POST', $uri, $options);
    }

    public function rawGet(string $uri, array $query = [], array $headers = []): array
    {
        return $this->get($uri, $query, $headers);
    }

    public function rawPost(string $uri, array $data = [], array $headers = []): array
    {
        return $this->post($uri, $data, $headers);
    }

    public function rawPostForm(string $uri, array $data = [], array $headers = []): array
    {
        return $this->postForm($uri, $data, $headers);
    }

    public function rawDelete(string $uri, array $data = [], array $headers = []): array
    {
        return $this->delete($uri, $data, $headers);
    }

    protected function request(string $method, string $uri, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $this->normalizeUri($uri), $options);
            $body = $response->getBody()->getContents();
            $decoded = json_decode($body, true);

            return is_array($decoded) ? $decoded : ['raw' => $body];
        } catch (RequestException $e) {
            $body = $e->getResponse()?->getBody()->getContents();
            $decoded = $body ? json_decode($body, true) : null;

            return [
                'error' => $decoded['meta']['message'] ?? $decoded['message'] ?? $e->getMessage(),
                'code' => $e->getCode(),
                'response' => is_array($decoded) ? $decoded : $body,
            ];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }
}
