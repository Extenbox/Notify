<?php

namespace Extenbox\Notify;

use Illuminate\Support\ServiceProvider;

class NotifakServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/notifak.php',
            'notifak'
        );

        $this->app->singleton('notifak', function ($app) {
            return new NotifakManager(
                $app['config']->get('notifak', [])
            );
        });

        $this->app->alias('notifak', NotifakManager::class);

        // بارگذاری helper functions
        if (file_exists($helpers = __DIR__ . '/helpers.php')) {
            require_once $helpers;
        }
    }

    public function boot(): void
    {
        // انتشار config
        $this->publishes([
            __DIR__ . '/../config/notifak.php' => config_path('notifak.php'),
        ], 'notifak-config');

        // انتشار migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'notifak-migrations');

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
