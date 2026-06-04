<?php

namespace Extenbox\Notify;

use Illuminate\Support\ServiceProvider;

class NotifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $root = dirname(__DIR__, 2);

        $this->mergeConfigFrom(
            $root . '/config/notify.php',
            'Notify'
        );

        $this->app->singleton('Notify', function ($app) {
            return new NotifyManager(
                $app['config']->get('Notify', [])
            );
        });

        $this->app->alias('Notify', NotifyManager::class);

        // بارگذاری helper functions
        if (file_exists($helpers = $root . '/src/helpers.php')) {
            require_once $helpers;
        }
    }

    public function boot(): void
    {
        $root = dirname(__DIR__, 2);

        // انتشار config
        $this->publishes([
            $root . '/config/notify.php' => config_path('Notify.php'),
        ], 'Notify-config');

        // انتشار migrations
        $this->publishes([
            $root . '/db/migrations/' => database_path('migrations'),
        ], 'Notify-migrations');

        // لود migrations به صورت خودکار
        $this->loadMigrationsFrom($root . '/db/migrations');

        // ثبت artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
                Console\TestSendCommand::class,
                Commands\ProviderStatusCommand::class,
            ]);
        }
    }
}
