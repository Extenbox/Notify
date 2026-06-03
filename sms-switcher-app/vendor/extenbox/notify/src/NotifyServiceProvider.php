<?php

namespace Extenbox\Notify;

use Illuminate\Support\ServiceProvider;

class NotifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/Notify.php',
            'Notify'
        );

        $this->app->singleton('Notify', function ($app) {
            return new NotifyManager(
                $app['config']->get('Notify', [])
            );
        });

        $this->app->alias('Notify', NotifyManager::class);

        // بارگذاری helper functions
        if (file_exists($helpers = __DIR__ . '/helpers.php')) {
            require_once $helpers;
        }
    }

    public function boot(): void
    {
        // انتشار config
        $this->publishes([
            __DIR__ . '/../config/Notify.php' => config_path('Notify.php'),
        ], 'Notify-config');

        // انتشار migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'Notify-migrations');

        // لود migrations به صورت خودکار
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // ثبت artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
                Console\TestSendCommand::class,
            ]);
        }
    }
}
