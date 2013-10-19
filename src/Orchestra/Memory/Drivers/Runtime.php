<?php namespace Orchestra\Memory\Drivers;

class Runtime extends Driver
{
    /**
     * Storage name.
     *
     * @var string
     */
    protected $storage = 'runtime';

    /**
     * No initialize method for runtime.
     *
     * @return boolean
     */
    public function initiate()
    {
        return true;
    }

    /**
     * No finish method for runtime.
     *
     * @return boolean
     */
    public function finish()
    {
        return true;
    }
}
