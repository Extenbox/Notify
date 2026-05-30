<?php

declare(strict_types=1);

namespace Extenbox\Notify\Support;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

class HttpClient
{
    public function __construct(private readonly ClientInterface $client = new Client())
    {
    }

    /**
     * @throws GuzzleException
     */
    public function postJson(string $url, array $payload = [], array $headers = []): mixed
    {
        return $this->decode($this->client->request('POST', $url, [
            'headers' => array_merge([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ], $headers),
            'json' => $payload,
        ])->getBody()->getContents());
    }

    /**
     * @throws GuzzleException
     */
    public function postForm(string $url, array $payload = [], array $headers = []): mixed
    {
        return $this->decode($this->client->request('POST', $url, [
            'headers' => array_merge([
                'Accept' => 'application/json',
            ], $headers),
            'form_params' => $payload,
        ])->getBody()->getContents());
    }

    private function decode(string $body): mixed
    {
        $decoded = json_decode($body, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $body;
    }
}
