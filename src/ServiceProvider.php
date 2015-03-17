<?php namespace Orchestra\Memory;

use Orchestra\Support\Providers\ServiceProvider as BaseServiceProvider;

abstract class ServiceProvider extends BaseServiceProvider
{
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
