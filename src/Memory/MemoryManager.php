<?php namespace Orchestra\Memory;

use Exception;
use Illuminate\Support\Arr;
use Orchestra\Support\Manager;

class MemoryManager extends Manager
{
    /**
     * Create Fluent driver.
     *
     * @param  string   $name
     * @return \Orchestra\Memory\Provider
     */
    protected function createFluentDriver($name)
    {
        $config  = $this->app['config']->get("orchestra/memory::fluent.{$name}", array());
        $cache   = $this->getCacheRepository($config);
        $handler = new FluentMemoryHandler($name, $config, $this->app['db'], $cache);

        return new Provider($handler);
    }

    /**
     * Create Eloquent driver.
     *
     * @param  string   $name
     * @return \Orchestra\Memory\Provider
     */
    protected function createEloquentDriver($name)
    {
        $config  = $this->app['config']->get("orchestra/memory::eloquent.{$name}", array());
        $cache   = $this->getCacheRepository($config);
        $handler = new EloquentMemoryHandler($name, $config, $this->app, $cache);

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
        $cache   = $this->getCacheRepository($config);
        $handler = new CacheMemoryHandler($name, $config, $cache);

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
     * Get the default driver.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']->get('orchestra/memory::driver', 'fluent.default');
    }

    /**
     * Set the default driver.
     *
     * @param  string   $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->app['config']->set('orchestra/memory::driver', $name);
    }

    /**
     * Make default driver or fallback to runtime.
     *
     * @param  string   $fallbackName
     * @return \Orchestra\Memory\Provider
     */
    public function makeOrFallback($fallbackName = 'orchestra')
    {
        $fallback = null;

        try {
            $fallback = $this->make();
        } catch (Exception $e) {
            $fallback = $this->driver("runtime.{$fallbackName}");
        }

        return $fallback;
    }

    /**
     * Loop every instance and execute finish method (if available).
     *
     * @return void
     */
    public function finish()
    {
        foreach ($this->drivers as $name => $class) {
            $class->finish();
            unset($this->drivers[$name]);
        }

        $this->drivers = array();
    }

    /**
     * Get cache repository.
     *
     * @param  array    $config
     * @return \Illuminate\Cache\Repository
     */
    protected function getCacheRepository(array $config)
    {
        $connection = Arr::get($config, 'connections.cache');

        return $this->app['cache']->driver($connection);
    }
}
