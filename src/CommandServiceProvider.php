<?php

namespace Orchestra\Memory;

use Orchestra\Memory\Console\MemoryCommand;
use Orchestra\Support\Providers\CommandServiceProvider as ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        'Memory' => 'orchestra.commands.memory',
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function registerMemoryCommand()
    {
        $this->app->singleton('orchestra.commands.memory', function () {
            return new MemoryCommand();
        });
    }
}
