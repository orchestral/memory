<?php namespace Orchestra\Memory;

trait ContainerTrait
{
    /**
     * Memory instance.
     *
     * @var \Orchestra\Memory\Provider
     */
    protected $memory = null;

    /**
     * Check whether a Memory instance is already attached to the container.
     *
     * @return boolean
     */
    public function attached()
    {
        return ($this->memory instanceof Provider);
    }

    /**
     * Attach memory provider.
     *
     * @param  \Orchestra\Memory\Provider  $memory
     * @return object
     */
    public function attach(Provider $memory)
    {
        $this->setMemoryProvider($memory);

        return $this;
    }

    /**
     * Set memory provider.
     *
     * @param  \Orchestra\Memory\Provider  $memory
     * @return object
     */
    public function setMemoryProvider(Provider $memory)
    {
        $this->memory = $memory;

        return $this;
    }

    /**
     * Set memory provider.
     *
     * @return \Orchestra\Memory\Provider|null
     */
    public function getMemoryProvider()
    {
        return $this->memory;
    }
}
