<?php namespace Orchestra\Memory\Handlers;

use Orchestra\Memory\Abstractable\Handler;
use Orchestra\Contracts\Memory\Handler as HandlerContract;

class Runtime extends Handler implements HandlerContract
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
        return [];
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
