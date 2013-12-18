<?php namespace Orchestra\Memory\Drivers;

use Orchestra\Support\Str;

class Eloquent extends Driver
{
    /**
     * Storage name.
     *
     * @var string
     */
    protected $storage = 'eloquent';

    /**
     * Load the data from database using Eloquent ORM.
     *
     * @return void
     */
    public function initiate()
    {
        $this->name = isset($this->config['model']) ? $this->config['model'] : $this->name;

        $memories = $this->app->make($this->name)->remember(60, $this->cacheKey)->all();

        foreach ($memories as $memory) {
            $value = Str::streamGetContents($memory->value);

            $this->put($memory->name, unserialize($value));

            $this->addKey($memory->name, array(
                'id'    => $memory->id,
                'value' => $value,
            ));
        }
    }

    /**
     * Add a finish event using Eloquent ORM.
     *
     * @return void
     */
    public function finish()
    {
        $changed = false;

        foreach ($this->data as $key => $value) {
            $isNew = $this->isNewKey($key);

            $serializedValue = serialize($value);

            if ($this->check($key, $serializedValue)) {
                continue;
            }

            $changed = true;

            $where = array('name', '=', $key);
            $count = call_user_func_array(array($this->name, 'where'), $where)->count();

            if (true === $isNew and $count < 1) {
                call_user_func(array($this->name, 'create'), array(
                    'name'  => $key,
                    'value' => $serializedValue,
                ));
            } else {
                $memory = call_user_func_array(array($this->name, 'where'), $where)->first();
                $memory->value = $serializedValue;

                $memory->save();
            }
        }

        $changed and $this->app['cache']->forget($this->cacheKey);
    }
}
