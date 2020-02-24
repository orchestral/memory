<?php

namespace Orchestra\Memory\Handlers;

use Illuminate\Contracts\Cache\Repository;
use Orchestra\Contracts\Memory\Handler as HandlerContract;
use Orchestra\Memory\Handler;

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
     */
    public function __construct(string $name, array $config, Repository $cache)
    {
        $this->cache = $cache;

        $name = $config['name'] ?? $name;

        parent::__construct($name, $config);
    }

    /**
     * Load the data from cache.
     */
    public function initiate(): array
    {
        return $this->cache->get("orchestra.memory.{$this->name}", []);
    }

    /**
     * Save data to cache.
     */
    public function finish(array $items = []): bool
    {
        $this->cache->forever("orchestra.memory.{$this->name}", $items);

        return true;
    }
}
