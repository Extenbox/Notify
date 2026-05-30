<?php

declare(strict_types=1);

namespace Extenbox\Notify\Laravel;

use Illuminate\Support\Facades\Facade;

class NotifyFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'notify';
    }
}
