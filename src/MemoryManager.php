<?php

namespace Orchestra\Memory;

use RuntimeException;
use Orchestra\Support\Manager;
use Orchestra\Memory\Handlers\Cache;
use Orchestra\Memory\Handlers\Fluent;
use Orchestra\Memory\Handlers\Runtime;
use Orchestra\Memory\Handlers\Eloquent;
use Illuminate\Contracts\Container\Container;
use Orchestra\Support\Concerns\WithConfiguration;
use Orchestra\Contracts\Memory\Handler as HandlerContract;
use Orchestra\Contracts\Memory\Provider as ProviderContract;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class MemoryManager extends Manager
{
    use WithConfiguration;

    /**
     * The encrypter implementation.
     *
     * @var \Illuminate\Contracts\Encryption\Encrypter|null
     */
    protected $encrypter;

    /**
     * Create a new manager instance.
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);

        try {
            $this->encrypter = $container->make('encrypter');
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
        $config = $this->configurations['fluent'][$name] ?? [];
        $cache = $this->getCacheRepository($config);

        return $this->createProvider(new Fluent($name, $config, $this->container->make('db'), $cache));
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
        $config = $this->configurations['eloquent'][$name] ?? [];
        $cache = $this->getCacheRepository($config);

        return $this->createProvider(new Eloquent($name, $config, $this->container, $cache));
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
        $config = $this->configurations['cache'][$name] ?? [];
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
        $config = $this->configurations['runtime'][$name] ?? [];

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
        return $this->configurations['driver'] ?? 'fluent.default';
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
        $this->configurations['driver'] = $name;
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
        return \rescue(function () {
            return $this->make();
        }, function () use ($fallbackName) {
            return $this->driver("runtime.{$fallbackName}");
        }, false);
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
        return $this->container->make('cache')->driver(
            $config['connections']['cache'] ?? null
        );
    }
}
