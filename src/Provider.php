<?php

namespace Orchestra\Memory;

use Illuminate\Contracts\Encryption\Encrypter;
use Orchestra\Contracts\Memory\Handler as HandlerContract;
use Orchestra\Contracts\Memory\Provider as ProviderContract;
use Orchestra\Support\Concerns\DataContainer;

class Provider implements ProviderContract
{
    use DataContainer;

    /**
     * Handler instance.
     *
     * @var \Orchestra\Contracts\Memory\Handler
     */
    protected $handler;

    /**
     * Construct an instance.
     */
    public function __construct(HandlerContract $handler, Encrypter $encrypter = null)
    {
        $this->handler = $handler;
        $this->encrypter = $encrypter;
        $this->items = $this->handler->initiate();
    }

    /**
     * Get handler instance.
     */
    public function getHandler(): HandlerContract
    {
        return $this->handler;
    }

    /**
     * Shutdown/finish method.
     */
    public function finish(): bool
    {
        return $this->handler->finish($this->allWithRemoved());
    }

    /**
     * Set a value from a key.
     *
     * @param  mixed  $value
     *
     * @return mixed
     */
    public function put(string $key, $value = '')
    {
        $this->set($key, $value);

        return $value;
    }

    /**
     * Set a value from a key.
     *
     * @param  mixed  $value
     *
     * @return mixed
     */
    public function securePut(string $key, $value = '')
    {
        $this->secureSet($key, $value);

        return $value;
    }
}
