<?php namespace Orchestra\Memory;

use Orchestra\Memory\Abstractable\Handler;

class RuntimeMemoryHandler extends Handler implements MemoryHandlerInterface
{
    /**
     * Storage name.
     *
     * @var string
     */
    protected $storage = 'runtime';

    /**
     * Load empty data for runtime.
     *
     * @return array
     */
    public function initiate()
    {
        return array();
    }

    /**
     * Save empty data to /dev/null.
     *
     * @param  array   $items
     * @return boolean
     */
    public function finish(array $items = array())
    {
        return true;
    }
}
