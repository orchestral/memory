<?php namespace Orchestra\Memory\Abstractable;

abstract class Handler
{
    /**
     * Memory name.
     *
     * @var string
     */
    protected $name;

    /**
     * Storage name.
     *
     * @var string
     */
    protected $storage;

    /**
     * Repository instance.
     *
     * @var object
     */
    protected $repository;

    /**
     * Cache instance.
     *
     * @var \Illuminate\Cache\Repository
     */
    protected $cache;

    /**
     * Cache key.
     *
     * @var string
     */
    protected $cacheKey;

    /**
     * Memory configuration.
     *
     * @var array
     */
    protected $config = array();

    /**
     * Cached key value map with md5 checksum.
     *
     * @var array
     */
    protected $keyMap = array();

    /**
     * Setup a new memory handler.
     *
     * @param  string  $name
     * @param  array   $config
     */
    public function __construct($name, array $config)
    {
        $this->name     = $name;
        $this->config   = array_merge($this->config, $config);
        $this->cacheKey = "db-memory:{$this->storage}-{$this->name}";
    }

    /**
     * Add key with id and checksum.
     *
     * @param  string   $name
     * @param  array    $option
     * @return void
     */
    protected function addKey($name, $option)
    {
        $option['checksum']  = md5($option['value']);
        $this->keyMap[$name] = $option;
    }

    /**
     * Is new key.
     *
     * @param  string   $name
     * @return integer
     */
    protected function getKeyId($name)
    {
        return array_get($this->keyMap, "{$name}.id");
    }

    /**
     * Get ID from key.
     *
     * @param  string   $name
     * @return boolean
     */
    protected function isNewKey($name)
    {
        return ! isset($this->keyMap[$name]);
    }

    /**
     * Verify checksum.
     *
     * @param  string   $name
     * @param  string   $check
     * @return boolean
     */
    protected function check($name, $check = '')
    {
        return (array_get($this->keyMap, "{$name}.checksum") === md5($check));
    }
}
