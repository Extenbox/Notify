<?php

declare(strict_types=1);

namespace Extenbox\Notify\Exceptions;

class ProviderNotFoundException extends NotifyException
{
    public static function for(string $provider): self
    {
        return new self(sprintf('SMS provider [%s] was not found.', $provider));
    }
}
