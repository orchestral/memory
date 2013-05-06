<?php namespace Orchestra\Memory;

use Closure;
use InvalidArgumentException;
use Illuminate\Support\Facades\Config;
use Orchestra\Support\Manager;

class MemoryManager extends Manager {
	
	/**
	 * Create Fluent Driver
	 *
	 * @access protected
	 * @param  string   $name
	 * @return Orchestra\Memory\Drivers\Fluent
	 */
	protected function createFluentDriver($name)
	{
		return new Drivers\Fluent($this->app, $name);
	}

	/**
	 * Create Eloquent Driver
	 *
	 * @access protected
	 * @param  string   $name
	 * @return Orchestra\Memory\Drivers\Eloquent
	 */
	protected function createEloquentDriver($name)
	{
		return new Drivers\Eloquent($this->app, $name);
	}

	/**
	 * Create Cache Driver
	 *
	 * @access protected
	 * @param  string   $name
	 * @return Orchestra\Memory\Drivers\Cache
	 */
	protected function createCacheDriver($name)
	{
		return new Drivers\Cache($this->app, $name);
	}

	/**
	 * Create Runtime Driver
	 *
	 * @access protected
	 * @param  string   $name
	 * @return Orchestra\Memory\Drivers\Runtime
	 */
	protected function createRuntimeDriver($name)
	{
		return new Drivers\Runtime($this->app, $name);
	}

	/**
	 * Create Default driver.
	 * 
	 * @access protected
	 * @param  string   $name
	 * @return Orchestra\Widget\Drivers\Placeholder
	 */
	protected function getDefaultDriver()
	{
		return $this->app['config']->get('orchestra/memory::config.driver', 'fluent.default');
	}

	/**
	 * Loop every instance and execute finish method (if available)
	 *
	 * @access  public
	 * @return  void
	 */
	public function finish()
	{
		foreach ($this->drivers as $class) $class->finish();

		$this->drivers = array();
	}
}
