<?php

namespace Orchestra\Memory\Handlers;

use Illuminate\Support\Arr;
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
    public function __construct($name, array $config, Repository $cache)
    {
        $this->cache = $cache;

        $name = Arr::get($config, 'name', $name);

        parent::__construct($name, $config);
    }

    /**
     * Load the data from cache.
     *
     * @return array
     */
    public function initiate()
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
    public function finish(array $items = [])
    {
        $this->cache->forever("orchestra.memory.{$this->name}", $items);

        return true;
    }
}
