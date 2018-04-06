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
    final public function attached(): bool
    {
        return $this->memory instanceof ProviderContract;
    }

    /**
     * Attach memory provider.
     *
     * @param  \Orchestra\Contracts\Memory\Provider  $memory
     *
     * @return $this
     */
    final public function attach(ProviderContract $memory): self
    {
        $this->setMemoryProvider($memory);

        return $this;
    }

    /**
     * Set memory provider.
     *
     * @param  \Orchestra\Contracts\Memory\Provider  $memory
     *
     * @return $this
     */
    final public function setMemoryProvider(ProviderContract $memory): self
    {
        $this->memory = $memory;

        return $this;
    }

    /**
     * Set memory provider.
     *
     * @return \Orchestra\Contracts\Memory\Provider|null
     */
    final public function getMemoryProvider(): ?ProviderContract
    {
        return $this->memory;
    }
}
