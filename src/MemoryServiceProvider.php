<?php namespace Orchestra\Memory;

use Orchestra\Support\Providers\ServiceProvider;
use Orchestra\Contracts\Config\PackageRepository;

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
            $manager = new MemoryManager($app);
            $namespace = ($app['config'] instanceof PackageRepository
                ? 'orchestra/memory::' : 'orchestra.memory');

            $manager->setConfig($app['config'][$namespace]);

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

        $this->addConfigComponent('orchestra/memory', 'orchestra/memory', $path.'/config');

        $this->bootUnderLaravel($path);

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

    /**
     * Boot under Laravel setup.
     *
     * @param  string  $path
     *
     * @return void
     */
    protected function bootUnderLaravel($path)
    {
        if (!$this->app['config'] instanceof PackageRepository) {
            $this->mergeConfigFrom("{$path}/config/config.php", 'orchestra.memory');

            $this->publishes([
                "{$path}/config/config.php"   => config_path('orchestra/memory.php'),
                "{$path}/database/migrations" => base_path('/database/migrations'),
            ]);
        }
    }
}
