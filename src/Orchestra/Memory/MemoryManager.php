<?php namespace Orchestra\Memory;

use Exception;
use Orchestra\Support\Manager;

class MemoryManager extends Manager
{
    /**
     * Create Fluent driver.
     *
     * @param  string   $name
     * @return \Orchestra\Memory\FluentMemoryHandler
     */
    protected function createFluentDriver($name)
    {
        $config  = $this->app['config']->get("orchestra/memory::fluent.{$name}", array());
        $handler = new FluentMemoryHandler($name, $config, $this->app['db'], $this->app['cache']);

        return new Provider($handler);
    }

    /**
     * Create Eloquent driver.
     *
     * @param  string   $name
     * @return \Orchestra\Memory\EloquentMemoryHandler
     */
    protected function createEloquentDriver($name)
    {
        $config  = $this->app['config']->get("orchestra/memory::eloquent.{$name}", array());
        $handler = new EloquentMemoryHandler($name, $config, $this->app, $this->app['cache']);

        return new Provider($handler);
    }

    /**
     * Create Cache driver.
     *
     * @param  string   $name
     * @return \Orchestra\Memory\Provider
     */
    protected function createCacheDriver($name)
    {
        $config  = $this->app['config']->get("orchestra/memory::cache.{$name}", array());
        $handler = new CacheMemoryHandler($name, $config, $this->app['cache']);

        return new Provider($handler);
    }

    /**
     * Create Runtime driver.
     *
     * @param  string   $name
     * @return \Orchestra\Memory\Provider
     */
    protected function createRuntimeDriver($name)
    {
        $config  = $this->app['config']->get("orchestra/memory::runtime.{$name}", array());
        $handler = new RuntimeMemoryHandler($name, $config);

        return new Provider($handler);
    }

    /**
     * Create Default driver.
     *
     * @return string
     */
    protected function getDefaultDriver()
    {
        return $this->app['config']->get('orchestra/memory::config.driver', 'fluent.default');
    }

    /**
     * Make default driver or fallback to runtime.
     *
     * @param  string   $fallbackName
     * @return \Orchestra\Memory\Drivers\Driver
     */
    public function makeOrFallback($fallbackName = 'orchestra')
    {
        try {
            return $this->make();
        } catch (Exception $e) {
            return $this->driver("runtime.{$fallbackName}");
        }
    }

    /**
     * Loop every instance and execute finish method (if available).
     *
     * @return void
     */
    public function finish()
    {
        foreach ($this->drivers as $class) {
            $class->finish();
        }

        $this->drivers = array();
    }
}
