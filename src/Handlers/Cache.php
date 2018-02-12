<?php

namespace Orchestra\Memory\Handlers;

use Orchestra\Memory\Handler;
use Illuminate\Contracts\Cache\Repository;
use Orchestra\Contracts\Memory\Handler as HandlerContract;

class Cache extends Handler implements HandlerContract
{
    /**
     * Storage name.
     *
     * @var string
     */
    protected $storage = 'cache';

    /**
     * Setup a new memory handler.
     *
     * @param  string  $name
     * @param  array  $config
     * @param  \Illuminate\Contracts\Cache\Repository  $cache
     */
    public function __construct(string $name, array $config, Repository $cache)
    {
        $this->cache = $cache;

        $name = $config['name'] ?? $name;

        parent::__construct($name, $config);
    }

    /**
     * Load the data from cache.
     *
     * @return array
     */
    public function initiate(): array
    {
        return $this->cache->get("orchestra.memory.{$this->name}", []);
    }

    /**
     * Save data to cache.
     *
     * @param  array  $items
     *
     * @return bool
     */
    public function finish(array $items = []): bool
    {
        $this->cache->forever("orchestra.memory.{$this->name}", $items);

        return true;
    }
}
