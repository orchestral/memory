<?php

namespace Orchestra\Memory\Handlers;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\DatabaseManager;
use Orchestra\Memory\DatabaseHandler;

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
     */
    public function __construct(string $name, array $config, DatabaseManager $repository, Repository $cache)
    {
        parent::__construct($name, $config);

        $this->repository = $repository;

        if (($this->config['cache'] ?? false)) {
            $this->cache = $cache;
        }
    }

    /**
     * Create/insert data to database.
     *
     * @param  mixed  $value
     */
    protected function save(string $key, $value, bool $isNew = false): void
    {
        $count = $this->resolver()->where('name', '=', $key)->count();
        $id = $this->getKeyId($key);

        if (true === $isNew && $count < 1) {
            $this->resolver()->insert([
                'name' => $key,
                'value' => $value,
            ]);
        } else {
            $this->resolver()->where('id', '=', $id)->update([
                'value' => $value,
            ]);
        }
    }

    /**
     * Remove data from database.
     */
    protected function delete(string $key): void
    {
        $this->resolver()->where('id', '=', $this->getKeyId($key))->delete();
    }

    /**
     * Get resolver instance.
     *
     * @return object
     */
    protected function resolver()
    {
        $table = $this->config['table'] ?? $this->name;

        return $this->repository->table($table);
    }
}
