<?php

namespace Extenbox\Notify\Exceptions;

class DriverNotFoundException extends NotifyException
{
    public static function for(string $name): static
    {
        return new static("درایور «{$name}» پشتیبانی نمی‌شود. درایورهای مجاز: mediana, melipayamak, ghasedak, smsir, ippanel");
    }
}
