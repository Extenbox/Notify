<?php

declare(strict_types=1);

namespace Extenbox\Notify;

use Illuminate\Support\ServiceProvider;

class NotifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Notify::class, fn () => new Notify());
        $this->app->alias(Notify::class, 'notify');
    }
}
