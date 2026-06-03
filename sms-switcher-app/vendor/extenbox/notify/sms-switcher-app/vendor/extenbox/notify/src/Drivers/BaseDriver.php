<?php

namespace Extenbox\Notify\Drivers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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
        $this->client = new Client([
            'base_uri' => $this->config['base_url'] ?? '',
            'timeout'  => 30,
            'headers'  => $this->defaultHeaders(),
        ]);
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
        // Rebuild client if base_url changed
        if (isset($config['base_url'])) {
            $this->client = new Client([
                'base_uri' => $config['base_url'],
                'timeout'  => 30,
                'headers'  => $this->defaultHeaders(),
            ]);
        }
        return $this;
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
        try {
            $options = ['json' => $data];
            if (!empty($headers)) {
                $options['headers'] = $headers;
            }

            $response = $this->client->post($uri, $options);
            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    /**
     * Helper: GET request
     */
    protected function get(string $uri, array $query = [], array $headers = []): array
    {
        try {
            $options = ['query' => $query];
            if (!empty($headers)) {
                $options['headers'] = $headers;
            }

            $response = $this->client->get($uri, $options);
            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }
}
