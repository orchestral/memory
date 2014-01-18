<?php namespace Orchestra\Memory;

use Illuminate\Cache\CacheManager;
use Illuminate\Database\DatabaseManager;
use Orchestra\Memory\Abstractable\DatabaseHandler;

class FluentMemoryHandler extends DatabaseHandler
{
    /**
     * Storage name.
     *
     * @var string
     */
    protected $storage = 'fluent';

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
     * @param  string                                 $name
     * @param  array                                  $config
     * @param  \Illuminate\Database\DatabaseManager   $repository
     * @param  \Illuminate\Cache\CacheManager         $cache
     */
    public function __construct($name, array $config, DatabaseManager $repository, CacheManager $cache)
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
        $count = $this->resolver()->where('name', '=', $key)->count();
        $id    = $this->getKeyId($key);

        if (true === $isNew && $count < 1) {
            $this->resolver()->insert(array(
                'name'  => $key,
                'value' => $value,
            ));
        } else {
            $this->resolver()->where('id', '=', $id)->update(array(
                'value' => $value,
            ));
        }
    }

    /**
     * Get resolver instance.
     *
     * @return object
     */
    protected function resolver()
    {
        $table = array_get($this->config, 'table', $this->name);

        return $this->repository->table($table);
    }
}
