<?php namespace Orchestra\Memory\Drivers;

use Orchestra\Support\Str;

class Eloquent extends Driver {

	/**
	 * Storage name
	 * 
	 * @access  protected
	 * @var     string  
	 */
	protected $storage = 'eloquent';

	/**
	 * Cached key value map with md5 checksum
	 *
	 * @access  protected
	 * @var     array
	 */
	protected $keyMap = array();

	/**
	 * Load the data from database using Eloquent ORM
	 *
	 * @access  public
	 * @return  void
	 */
	public function initiate() 
	{
		$this->name = isset($this->config['model']) ? $this->config['model'] : $this->name;
		
		$memories = call_user_func(array($this->name, 'all'));

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
	 * Add a shutdown event using Eloquent ORM
	 *
	 * @access  public
	 * @return  void
	 */
	public function shutdown() 
	{
		foreach ($this->data as $key => $value)
		{
			$isNew = $this->isNewKey($key);
			$id    = $this->getKeyId($key);

			$serializedValue = serialize($value);

			if ($this->check($key, $serializedValue)) continue;

			$where = array('name', '=', $key);
			$count = call_user_func_array(array($this->name, 'where'), $where)->count();

			if (true === $isNew and $count < 1)
			{
				call_user_func(array($this->name, 'create'), array(
					'name'  => $key,
					'value' => $serializedValue,
				));
			}
			else
			{
				$memory = call_user_func_array(array($this->name, 'where'), $where)->first();
				$memory->value = $serializedValue;

				$memory->save();
			}
		}
	}
}
