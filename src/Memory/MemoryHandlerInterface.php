<?php namespace Orchestra\Memory;

interface MemoryHandlerInterface
{
    /**
     * Initialize method.
     *
     * @return array
     */
    public function initiate();

    /**
     * Shutdown method.
     *
     * @param  array   $items
     * @return boolean
     */
    public function finish(array $items = array());
}
