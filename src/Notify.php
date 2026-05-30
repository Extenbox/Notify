<?php

declare(strict_types=1);

namespace Extenbox\Notify;

use Extenbox\Notify\Contracts\SmsProviderInterface;

class Notify
{
    public array $config = [];

    protected ProviderManager $manager;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->manager = new ProviderManager($config);
    }

    public function config(array $config): static
    {
        $this->config = $config;
        $this->manager->config($config);

        return $this;
    }

    public function provider(string $name): SmsProviderInterface
    {
        $this->manager->config($this->config);

        return $this->manager->provider($name);
    }

    public function extend(string $name, string|SmsProviderInterface $provider): static
    {
        $this->manager->extend($name, $provider);

        return $this;
    }
}
