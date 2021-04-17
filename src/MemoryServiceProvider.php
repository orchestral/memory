<?php

namespace Orchestra\Memory;

use Illuminate\Contracts\Container\Container;
use Laravel\Octane\Events\RequestReceived;
use Orchestra\Support\Providers\ServiceProvider;

class MemoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('orchestra.memory', function (Container $app) {
            return new MemoryManager($app);
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $path = \realpath(__DIR__.'/../');

        $this->addConfigComponent('orchestra/memory', 'orchestra/memory', "{$path}/config");

        if (! $this->hasPackageRepository()) {
            $this->bootUnderLaravel($path);
        }

        $this->loadMigrationsFrom([
            "{$path}/database/migrations",
        ]);

        $this->bootEvents();
    }

    /**
     * Register memory events during booting.
     */
    protected function bootEvents(): void
    {
        $this->callAfterResolving('orchestra.memory', function ($manager, $app) {
            $namespace = $this->hasPackageRepository() ? 'orchestra/memory::' : 'orchestra.memory';

            $manager->setConfiguration($app->make('config')->get($namespace));
        });

        $this->app['events']->listen(RequestReceived::class, function ($event) {
            $event->sandbox->make('orchestra.memory')->finish();
        });

        $this->app->terminating(function () {
            app('orchestra.memory')->finish();
        });
    }

    /**
     * Boot under Laravel setup.
     */
    protected function bootUnderLaravel(string $path): void
    {
        $this->mergeConfigFrom("{$path}/config/config.php", 'orchestra.memory');

        $this->publishes([
            "{$path}/config/config.php" => \config_path('orchestra/memory.php'),
        ]);
    }
}
