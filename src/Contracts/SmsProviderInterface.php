<?php

declare(strict_types=1);

namespace Extenbox\Notify\Contracts;

interface SmsProviderInterface
{
    public function config(array $config): static;

    public function send(string|array $to, string $message, array $options = []): mixed;

    public function pattern(string|array $to, string $pattern, array $params = [], array $options = []): mixed;
}
