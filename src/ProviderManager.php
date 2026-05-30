<?php

declare(strict_types=1);

namespace Extenbox\Notify;

use Extenbox\Notify\Contracts\SmsProviderInterface;
use Extenbox\Notify\Exceptions\ProviderNotFoundException;
use Extenbox\Notify\Providers\Ghasedak;
use Extenbox\Notify\Providers\Ippanel;
use Extenbox\Notify\Providers\Melipayamak;

class ProviderManager
{
    protected array $providers = [
        'ippanel' => Ippanel::class,
        'mediana' => Ippanel::class,
        'melipayamak' => Melipayamak::class,
        'meli-payamak' => Melipayamak::class,
        'ghasedak' => Ghasedak::class,
    ];

    protected array $instances = [];

    public function __construct(protected array $config = [])
    {
    }

    public function config(array $config): static
    {
        $this->config = $config;
        $this->instances = [];

        return $this;
    }

    public function extend(string $name, string|SmsProviderInterface $provider): static
    {
        $this->providers[$this->normalize($name)] = $provider;
        unset($this->instances[$this->normalize($name)]);

        return $this;
    }

    public function provider(string $name): SmsProviderInterface
    {
        $name = $this->normalize($name);

        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        $provider = $this->providers[$name] ?? null;

        if (! $provider) {
            throw ProviderNotFoundException::for($name);
        }

        $instance = is_string($provider) ? new $provider() : $provider;
        $instance->config($this->config[$name] ?? []);

        return $this->instances[$name] = $instance;
    }

    protected function normalize(string $name): string
    {
        return strtolower(trim($name));
    }
}
