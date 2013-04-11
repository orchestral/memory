<?php namespace Orchestra\Memory;

use Closure,
	InvalidArgumentException,
	Illuminate\Support\Facades\Config;

class Environment {
	/**
	 * The third-party driver registrar.
	 *
	 * @var array
	 */
	public $registrar = array();

	/**
	 * Cache memory instance so we can reuse it
	 * 
	 * @var array
	 */
	protected $instances = array();

	/**
	 * Construct a new Memory Environment.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		$this->registrar = array();
		$this->instances = array();
	}

	/**
	 * Register a third-party memory driver.
	 *
	 * @access public
	 * @param  string   $driver
	 * @param  Closure  $resolver
	 * @return void
	 */
	public function extend($driver, Closure $resolver)
	{
		$this->registrar[$driver] = $resolver;
	}

	/**
	 * Initiate a new Memory instance
	 * 
	 * @access  public
	 * @param   string  $name      instance name
	 * @param   array   $config
	 * @return  Memory
	 * @throws  Exception
	 */
	public function make($name = null, $config = array())
	{
		is_null($name) and $name = 'runtime.default';
		(false === strpos($name, '.')) and $name = $name.'.default';

		list($storage, $driver) = explode('.', $name, 2);

		$name = $storage.'.'.$driver;
		
		if ( ! isset($this->instances[$name]))
		{
			if (isset($this->registrar[$storage]))
			{
				$resolver = $this->registrar[$storage];

				return $this->instances[$name] = $resolver($driver, $config);
			}

			switch ($storage)
			{
				case 'fluent' :
					if ($driver === 'default') $driver = Config::get('orchestra::memory.default_table');
					$this->instances[$name] = new Drivers\Fluent($driver, $config);
					break;
				case 'eloquent' :
					if ($driver === 'default') $driver = Config::get('orchestra::memory.default_model');
					$this->instances[$name] = new Drivers\Eloquent($driver, $config);
					break;
				case 'cache' :
					$this->instances[$name] = new Drivers\Cache($driver, $config);
					break;
				case 'runtime' :
					$this->instances[$name] = new Drivers\Runtime($driver, $config);
					break;
				default :
					throw new InvalidArgumentException(
						"Requested Orchestra\Memory Driver [$storage] does not exist."
					);
			}
		}

		return $this->instances[$name];
	}

	/**
	 * Loop every instance and execute shutdown method (if available)
	 *
	 * @access  public
	 * @return  void
	 */
	public function shutdown()
	{
		foreach ($this->instances as $class) $class->shutdown();

		$this->instances = array();
	}
}