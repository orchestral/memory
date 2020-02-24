<?php

namespace Orchestra\Memory\Handlers;

use Orchestra\Contracts\Memory\Handler as HandlerContract;
use Orchestra\Memory\Handler;

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
     */
    public function initiate(): array
    {
        return [];
    }

    /**
     * Save empty data to /dev/null.
     */
    public function finish(array $items = []): bool
    {
        return true;
    }
}
