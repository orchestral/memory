<?php namespace Orchestra\Memory\Drivers;

class Cache extends Driver {
	/**
	 * Storage name
	 * 
	 * @access  protected
	 * @var     string  
	 */
	protected $storage = 'cache';

	/**
	 * Load the data from database using Cache
	 *
	 * @access  public
	 * @return  void
	 */
	public function initiate() 
	{
		$this->name = isset($this->config['name']) ? $this->config['name'] : $this->name;
		$this->data = $this->app['cache']->get('orchestra.memory.'.$this->name, array());
	}
	
	/**
	 * Add a shutdown event using Cache
	 *
	 * @access  public
	 * @return  void
	 */
	public function shutdown() 
	{
		$this->app['cache']->forever('orchestra.memory.'.$this->name, $this->data);
	}
}
