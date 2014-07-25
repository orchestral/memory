<?php namespace Orchestra\Memory;

use Orchestra\Support\Relic;

class Provider extends Relic
{
    /**
     * Handler instance.
     *
     * @var MemoryHandlerInterface
     */
    protected $handler;

    /**
     * Construct an instance.
     *
     * @param  MemoryHandlerInterface   $handler
     */
    public function __construct(MemoryHandlerInterface $handler)
    {
        $this->handler = $handler;

        $this->items = $this->handler->initiate();
    }

    /**
     * Get handler instance.
     *
     * @return Abstractable\Handler
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Shutdown/finish method.
     *
     * @return bool
     */
    public function finish()
    {
        return $this->handler->finish($this->items);
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
        $this->set($key, $value);

        return $value;
    }
}
