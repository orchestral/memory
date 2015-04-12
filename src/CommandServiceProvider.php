<?php namespace Orchestra\Memory;

use Illuminate\Support\ServiceProvider;
use Orchestra\Memory\Console\MemoryCommand;

class CommandServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('orchestra.commands.memory', function () {
            return new MemoryCommand();
        });

        $this->commands('orchestra.commands.memory');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['orchestra.commands.memory'];
    }
}
