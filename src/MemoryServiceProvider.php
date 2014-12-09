<?php namespace Orchestra\Memory;

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
        $this->app->singleton('orchestra.memory', function ($app) {
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
        $path = realpath(__DIR__.'/../resources');

        $this->addConfigComponent('orchestra/memory', 'orchestra/memory', $path.'/config');

        $this->registerMemoryEvent();
    }

    /**
     * Register memory events during booting.
     *
     * @return void
     */
    protected function registerMemoryEvent()
    {
        $app = $this->app;

        $app['router']->after(function () use ($app) {
            $app['orchestra.memory']->finish();
        });
    }
}
