<?php namespace Orchestra\Memory;

class LaravelServiceProvider extends MemoryServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('orchestra.memory', function ($app) {
            $manager = new MemoryManager($app);

            $manager->setConfig($app['config']['orchestra.memory']);

            return $manager;
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $path = realpath(__DIR__.'/../resources');

        $this->publishes([
            "{$path}/config/config.php"   => config_path('orchestra/memory.php'),
            "{$path}/database/migrations" => base_path('/database/migrations'),
        ]);

        $this->bootMemoryEvent();
    }

    /**
     * Register memory events during booting.
     *
     * @return void
     */
    protected function bootMemoryEvent()
    {
        $app = $this->app;

        $app->terminating(function () use ($app) {
            $app['orchestra.memory']->finish();
        });
    }
}
