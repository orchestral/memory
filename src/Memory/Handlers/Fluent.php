<?php namespace Orchestra\Memory\Handlers;

use Illuminate\Support\Arr;
use Illuminate\Cache\Repository;
use Orchestra\Memory\DatabaseHandler;
use Illuminate\Database\DatabaseManager;

class Fluent extends DatabaseHandler
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
    protected $config = [
        'cache' => false,
    ];

    /**
     * Setup a new memory handler.
     *
     * @param  string  $name
     * @param  array  $config
     * @param  \Illuminate\Database\DatabaseManager  $repository
     * @param  \Illuminate\Cache\Repository  $cache
     */
    public function __construct($name, array $config, DatabaseManager $repository, Repository $cache)
    {
        parent::__construct($name, $config);

        $this->repository = $repository;

        if (Arr::get($this->config, 'cache', false)) {
            $this->cache = $cache;
        }
    }

    /**
     * Create/insert data to database.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  bool    $isNew
     * @return bool
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
            $this->resolver()->where('id', '=', $id)->update([
                'value' => $value,
            ]);
        }
    }

    /**
     * Get resolver instance.
     *
     * @return object
     */
    protected function resolver()
    {
        $table = Arr::get($this->config, 'table', $this->name);

        return $this->repository->table($table);
    }
}
