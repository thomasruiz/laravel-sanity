<?php

namespace LaravelSanity;

use Illuminate\Support\ServiceProvider;
use LaravelSanity\Console\SanityCommand;

class LaravelSanityServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../resources/config/checks.php' => config_path('checks.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands(SanityCommand::class);
        }
    }

    public function register()
    {

    }
}
