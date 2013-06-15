<?php namespace Orchestra\Memory\Drivers;

use Orchestra\Support\Str;

class Fluent extends Driver {
	
	/**
	 * Storage name.
	 * 
	 * @var string  
	 */
	protected $storage = 'fluent';

	/**
	 * Database table name.
	 * 
	 * @var string
	 */
	protected $table = null;

	/**
	 * Load the data from database using Fluent Query Builder.
	 *
	 * @access public
	 * @return void
	 */
	public function initiate() 
	{
		$this->table = isset($this->config['table']) ? $this->config['table'] : $this->name;
		
		$memories = $this->app['db']->table($this->table)->get();

		foreach ($memories as $memory)
		{
			$value = Str::streamGetContents($memory->value);

			$this->put($memory->name, unserialize($value));

			$this->addKey($memory->name, array(
				'id'    => $memory->id,
				'value' => $value,
			));
		}
	}

	/**
	 * Add a finish event using Fluent Query Builder.
	 *
	 * @access public
	 * @return void
	 */
	public function finish() 
	{
		foreach ($this->data as $key => $value)
		{
			$isNew = $this->isNewKey($key);
			$id    = $this->getKeyId($key);

			$serializedValue = serialize($value);

			if ($this->check($key, $serializedValue)) continue;

			$count = $this->app['db']->table($this->table)->where('name', '=', $key)->count();

			if (true === $isNew and $count < 1)
			{
				$this->app['db']->table($this->table)->insert(array(
					'name'  => $key,
					'value' => $serializedValue,
				));
			}
			else
			{
				$this->app['db']->table($this->table)->where('id', '=', $id)->update(array(
					'value' => $serializedValue,
				)); 
			}
		}
	}
}
