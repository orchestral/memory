<?php namespace Orchestra\Memory;

use Illuminate\Container\Container;
use Illuminate\Cache\CacheManager;
use Orchestra\Memory\Abstractable\DatabaseHandler;

class EloquentMemoryHandler extends DatabaseHandler
{
    /**
     * Storage name.
     *
     * @var string
     */
    protected $storage = 'eloquent';

    /**
     * Memory configuration.
     *
     * @var array
     */
    protected $config = array(
        'cache' => false,
    );

    /**
     * Setup a new memory handler.
     *
     * @param  string                           $name
     * @param  array                            $config
     * @param  \Illuminate\Container\Container  $repository
     * @param  \Illuminate\Cache\CacheManager   $cache
     */
    public function __construct($name, array $config, Container $repository, CacheManager $cache)
    {
        parent::__construct($name, $config);

        $this->repository = $repository;

        if (array_get($this->config, 'cache', false)) {
            $this->cache = $cache;
        }
    }

    /**
     * Create/insert data to database.
     *
     * @param  array   $items
     * @return boolean
     */
    protected function save($key, $value, $isNew = false)
    {
        $model = $this->resolver()->where('name', '=', $key)->first();

        if (true === $isNew && is_null($model)) {
            $this->resolver()->create(array(
                'name'  => $key,
                'value' => $value,
            ));
        } else {
            $model->value = $value;

            $model->save();
        }
    }

    /**
     * Get resolver instance.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function resolver()
    {
        $model = array_get($this->config, 'model', $this->name);

        return $this->repository->make($model)->newInstance();
    }
}
