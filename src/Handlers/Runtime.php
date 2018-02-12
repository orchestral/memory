<?php

namespace Orchestra\Memory\Handlers;

use Orchestra\Memory\Handler;
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
    public function initiate(): array
    {
        return [];
    }

    /**
     * Save empty data to /dev/null.
     *
     * @param  array  $items
     *
     * @return bool
     */
    public function finish(array $items = []): bool
    {
        return true;
    }
}
