<?php

namespace Orchestra\Memory;

use Exception;
use RuntimeException;
use Orchestra\Support\Manager;
use Orchestra\Memory\Handlers\Cache;
use Orchestra\Memory\Handlers\Fluent;
use Orchestra\Memory\Handlers\Runtime;
use Orchestra\Memory\Handlers\Eloquent;
use Orchestra\Contracts\Memory\Handler as HandlerContract;
use Orchestra\Contracts\Memory\Provider as ProviderContract;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

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
    protected function createFluentDriver(string $name): ProviderContract
    {
        $config = $this->config['fluent'][$name] ?? [];
        $cache = $this->getCacheRepository($config);

        return $this->createProvider(new Fluent($name, $config, $this->app->make('db'), $cache));
    }

    /**
     * Create Eloquent driver.
     *
     * @param  string  $name
     *
     * @return \Orchestra\Contracts\Memory\Provider
     */
    protected function createEloquentDriver(string $name): ProviderContract
    {
        $config = $this->config['eloquent'][$name] ?? [];
        $cache = $this->getCacheRepository($config);

        return $this->createProvider(new Eloquent($name, $config, $this->app, $cache));
    }

    /**
     * Create Cache driver.
     *
     * @param  string  $name
     *
     * @return \Orchestra\Contracts\Memory\Provider
     */
    protected function createCacheDriver(string $name): ProviderContract
    {
        $config = $this->config['cache'][$name] ?? [];
        $cache = $this->getCacheRepository($config);

        return $this->createProvider(new Cache($name, $config, $cache));
    }

    /**
     * Create Runtime driver.
     *
     * @param  string  $name
     *
     * @return \Orchestra\Contracts\Memory\Provider
     */
    protected function createRuntimeDriver(string $name): ProviderContract
    {
        $config = $this->config['runtime'][$name] ?? [];

        return $this->createProvider(new Runtime($name, $config));
    }

    /**
     * Create a memory provider.
     *
     * @param  \Orchestra\Contracts\Memory\Handler  $handler
     *
     * @return \Orchestra\Contracts\Memory\Provider
     */
    protected function createProvider(HandlerContract $handler): ProviderContract
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
        return $this->config['driver'] ?? 'fluent.default';
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
    public function setConfig(array $config): self
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
    public function makeOrFallback(string $fallbackName = 'orchestra'): ProviderContract
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
    public function finish(): void
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
    protected function getCacheRepository(array $config): CacheRepository
    {
        $connection = $config['connections']['cache'] ?? null;

        return $this->app->make('cache')->driver($connection);
    }
}
