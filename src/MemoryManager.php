<?php

namespace Orchestra\Memory;

use Exception;
use RuntimeException;
use Illuminate\Support\Arr;
use Orchestra\Support\Manager;
use Orchestra\Memory\Handlers\Cache;
use Orchestra\Memory\Handlers\Fluent;
use Orchestra\Memory\Handlers\Runtime;
use Orchestra\Memory\Handlers\Eloquent;
use Orchestra\Contracts\Memory\Handler as HandlerContract;

class MemoryManager extends Manager
{
    /**
     * Configuration values.
     *
     * @var array
     */
    protected $config = [];

    /**
     * The encrypter implementation.
     *
     * @var \Illuminate\Contracts\Encryption\Encrypter|null
     */
    protected $encrypter;

    /**
     * Create a new manager instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function __construct($app)
    {
        parent::__construct($app);

        try {
            $this->encrypter = $app->make('encrypter');
        } catch (RuntimeException $e) {
            $this->encrypter = null;
        }
    }

    /**
     * Create Fluent driver.
     *
     * @param  string  $name
     *
     * @return \Orchestra\Contracts\Memory\Provider
     */
    protected function createFluentDriver($name)
    {
        $config = Arr::get($this->config, "fluent.{$name}", []);
        $cache  = $this->getCacheRepository($config);

        return $this->createProvider(new Fluent($name, $config, $this->app->make('db'), $cache));
    }

    /**
     * Create Eloquent driver.
     *
     * @param  string  $name
     *
     * @return \Orchestra\Contracts\Memory\Provider
     */
    protected function createEloquentDriver($name)
    {
        $config = Arr::get($this->config, "eloquent.{$name}", []);
        $cache  = $this->getCacheRepository($config);

        return $this->createProvider(new Eloquent($name, $config, $this->app, $cache));
    }

    /**
     * Create Cache driver.
     *
     * @param  string  $name
     *
     * @return \Orchestra\Contracts\Memory\Provider
     */
    protected function createCacheDriver($name)
    {
        $config = Arr::get($this->config, "cache.{$name}", []);
        $cache  = $this->getCacheRepository($config);

        return $this->createProvider(new Cache($name, $config, $cache));
    }

    /**
     * Create Runtime driver.
     *
     * @param  string  $name
     *
     * @return \Orchestra\Contracts\Memory\Provider
     */
    protected function createRuntimeDriver($name)
    {
        $config = Arr::get($this->config, "runtime.{$name}", []);

        return $this->createProvider(new Runtime($name, $config));
    }

    /**
     * Create a memory provider.
     *
     * @param  \Orchestra\Contracts\Memory\Handler  $handler
     *
     * @return \Orchestra\Contracts\Memory\Provider
     */
    protected function createProvider(HandlerContract $handler)
    {
        return new Provider($handler, $this->encrypter);
    }

    /**
     * Get the default driver.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return Arr::get($this->config, 'driver', 'fluent.default');
    }

    /**
     * Set the default driver.
     *
     * @param  string  $name
     *
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->config['driver'] = $name;
    }

    /**
     * Get configuration values.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set configuration.
     *
     * @param  array  $config
     *
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Make default driver or fallback to runtime.
     *
     * @param  string  $fallbackName
     *
     * @return \Orchestra\Contracts\Memory\Provider
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

        $this->drivers = [];
    }

    /**
     * Get cache repository.
     *
     * @param  array  $config
     *
     * @return \Illuminate\Contracts\Cache\Repository
     */
    protected function getCacheRepository(array $config)
    {
        $connection = Arr::get($config, 'connections.cache');

        return $this->app->make('cache')->driver($connection);
    }
}
