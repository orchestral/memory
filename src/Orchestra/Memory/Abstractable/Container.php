<?php namespace Orchestra\Memory\Abstractable;

use Orchestra\Memory\Drivers\Driver as MemoryDriver;

abstract class Container {

	/**
	 * Memory instance.
	 * 
	 * @var \Orchestra\Memory\Drivers\Driver
	 */
	protected $memory = null;

	/**
	 * Check whether a Memory instance is already attached to the container.
	 *
	 * @return boolean
	 */
	public function attached()
	{
		return ( ! is_null($this->memory));
	}

	/**
	 * Attach memory provider.
	 *
	 * @return self
	 */
	public function attach(MemoryDriver $memory)
	{
		$this->setMemoryProvider($memory);

		return $this;
	}

	/**
	 * Set memory provider.
	 *
	 * @param  \Orchestra\Memory\Drivers\Driver 
	 * @return self
	 */
	public function setMemoryProvider(MemoryDriver $memory)
	{
		$this->memory = $memory;

		return $this;
	}

	/**
	 * Set memory provider.
	 *
	 * @return \Orchestra\Memory\Drivers\Driver 
	 */
	public function getMemoryProvider()
	{
		return $this->memory;
	}
}
