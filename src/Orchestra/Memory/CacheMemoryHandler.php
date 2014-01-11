<?php namespace Orchestra\Memory;

use Illuminate\Cache\CacheManager;
use Orchestra\Memory\Abstractable\Handler;

class CacheMemoryHandler extends Handler implements MemoryHandlerInterface
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
     * @param  string                          $name
     * @param  array                           $config
     * @param  \Illuminate\Cache\CacheManager  $cache
     */
    public function __construct($name, array $config, CacheManager $cache)
    {
        $this->cache = $cache;

        $name = array_get($config, 'name', $name);

        parent::__construct($name, $config);
    }

    /**
     * Load the data from cache.
     *
     * @return array
     */
    public function initiate()
    {
        return $this->cache->get("orchestra.memory.{$this->name}", array());
    }

    /**
     * Save data to cache.
     *
     * @param  array   $items
     * @return boolean
     */
    public function finish(array $items = array())
    {
        $this->cache->forever("orchestra.memory.{$this->name}", $items);

        return true;
    }
}