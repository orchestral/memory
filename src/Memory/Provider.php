<?php namespace Orchestra\Memory;

use Orchestra\Support\Traits\DataContainerTrait;
use Orchestra\Contracts\Memory\Handler as HandlerContract;
use Orchestra\Contracts\Memory\Provider as ProviderContract;

class Provider implements ProviderContract
{
    use DataContainerTrait;

    /**
     * Handler instance.
     *
     * @var \Orchestra\Contracts\Memory\Handler
     */
    protected $handler;

    /**
     * Construct an instance.
     *
     * @param  \Orchestra\Contracts\Memory\Handler  $handler
     */
    public function __construct(HandlerContract $handler)
    {
        $this->handler = $handler;

        $this->items = $this->handler->initiate();
    }

    /**
     * Get handler instance.
     *
     * @return \Orchestra\Contracts\Memory\Handler
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
