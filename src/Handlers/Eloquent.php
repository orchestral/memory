<?php

namespace Orchestra\Memory\Handlers;

use Orchestra\Memory\DatabaseHandler;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\Container;

class Eloquent extends DatabaseHandler
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
    protected $config = [
        'cache' => false,
    ];

    /**
     * Setup a new memory handler.
     *
     * @param  string  $name
     * @param  array  $config
     * @param  \Illuminate\Contracts\Container\Container  $repository
     * @param  \Illuminate\Contracts\Cache\Repository  $cache
     */
    public function __construct(string $name, array $config, Container $repository, Repository $cache)
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
     * @param  string  $key
     * @param  mixed  $value
     * @param  bool  $isNew
     *
     * @return void
     */
    protected function save(string $key, $value, bool $isNew = false): void
    {
        $model = $this->resolver()->where('name', '=', $key)->first();

        if (true === $isNew && \is_null($model)) {
            $this->resolver()->create([
                'name' => $key,
                'value' => $value,
            ]);
        } else {
            $model->value = $value;

            $model->save();
        }
    }

    /**
     * Remove data from database.
     *
     * @param  string  $key
     *
     * @return void
     */
    protected function delete(string $key): void
    {
        $this->resolver()->where('id', '=', $this->getKeyId($key))->delete();
    }

    /**
     * Get resolver instance.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function resolver()
    {
        $model = $this->config['model'] ?? $this->name;

        return $this->repository->make($model)->newInstance();
    }
}
