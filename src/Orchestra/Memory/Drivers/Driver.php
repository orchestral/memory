<?php namespace Orchestra\Memory\Drivers;

use Illuminate\Container\Container;

abstract class Driver
{
    /**
     * Application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app = null;

    /**
     * Memory name.
     *
     * @var string
     */
    protected $name = null;

    /**
     * Memory configuration.
     *
     * @var array
     */
    protected $config = array();

    /**
     * Collection of key-value pair of either configuration or data.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Cached key value map with md5 checksum.
     *
     * @var array
     */
    protected $keyMap = array();

    /**
     * Storage name.
     *
     * @var string
     */
    protected $storage;

    /**
     * Construct an instance.
     *
     * @param  \Illuminate\Foundation\Application   $app
     * @param  string                               $name
     * @return void
     */
    public function __construct(Container $app, $name = 'default')
    {
        $this->app    = $app;
        $this->name   = $name;
        $this->config = array_merge(
            $app['config']->get("orchestra/memory::{$this->storage}.{$name}", array()),
            $this->config
        );

        $this->initiate();
    }

    /**
     * Get value of a key.
     *
     * @param  string   $key        A string of key to search.
     * @param  mixed    $default    Default value if key doesn't exist.
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        $value = array_get($this->data, $key, null);

        if (! is_null($value)) {
            return $value;
        }

        return value($default);
    }

    /**
     * Set a value from a key.
     *
     * @param  string   $key    A string of key to add the value.
     * @param  mixed    $value  The value.
     * @return mixed
     */
    public function put($key, $value = '')
    {
        $value = value($value);
        array_set($this->data, $key, $value);

        return $value;
    }

    /**
     * Delete value of a key.
     *
     * @param  string   $key    A string of key to delete.
     * @return boolean
     */
    public function forget($key = null)
    {
        return array_forget($this->data, $key);
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

    /**
     * Initialize method.
     *
     * @return void
     */
    abstract public function initiate();

    /**
     * Shutdown method.
     *
     * @return void
     */
    abstract public function finish();
}
