<?php

namespace Orchestra\Memory;

use Orchestra\Support\Concerns\DataContainer;
use Illuminate\Contracts\Encryption\Encrypter;
use Orchestra\Contracts\Memory\Handler as HandlerContract;
use Orchestra\Contracts\Memory\Provider as ProviderContract;

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
     *
     * @param  \Orchestra\Contracts\Memory\Handler  $handler
     * @param  \Illuminate\Contracts\Encryption\Encrypter|null  $encrypter
     */
    public function __construct(HandlerContract $handler, Encrypter $encrypter = null)
    {
        $this->handler = $handler;
        $this->encrypter = $encrypter;
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
    public function finish(): bool
    {
        return $this->handler->finish($this->allWithRemoved());
    }

    /**
     * Set a value from a key.
     *
     * @param  string  $key    a string of key to add the value
     * @param  mixed   $value  the value
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
     * @param  string  $key    a string of key to add the value
     * @param  mixed   $value  the value
     *
     * @return mixed
     */
    public function securePut(string $key, $value = '')
    {
        $this->secureSet($key, $value);

        return $value;
    }
}
