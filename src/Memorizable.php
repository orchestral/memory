<?php

namespace Orchestra\Memory;

use Orchestra\Contracts\Memory\Provider as ProviderContract;

trait Memorizable
{
    /**
     * Memory instance.
     *
     * @var \Orchestra\Contracts\Memory\Provider
     */
    protected $memory = null;

    /**
     * Check whether a Memory instance is already attached to the container.
     *
     * @return bool
     */
    public function attached()
    {
        return ($this->memory instanceof ProviderContract);
    }

    /**
     * Attach memory provider.
     *
     * @param  \Orchestra\Contracts\Memory\Provider  $memory
     *
     * @return object
     */
    public function attach(ProviderContract $memory)
    {
        $this->setMemoryProvider($memory);

        return $this;
    }

    /**
     * Set memory provider.
     *
     * @param  \Orchestra\Contracts\Memory\Provider  $memory
     *
     * @return object
     */
    public function setMemoryProvider(ProviderContract $memory)
    {
        $this->memory = $memory;

        return $this;
    }

    /**
     * Set memory provider.
     *
     * @return \Orchestra\Contracts\Memory\Provider|null
     */
    public function getMemoryProvider()
    {
        return $this->memory;
    }
}
