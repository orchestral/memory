<?php namespace Orchestra\Memory\Handlers;

use Illuminate\Support\Arr;
use Illuminate\Cache\Repository;
use Orchestra\Memory\DatabaseHandler;
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
     * @param  string                                      $name
     * @param  array                                       $config
     * @param  \Illuminate\Contracts\Container\Container   $repository
     * @param  \Illuminate\Cache\Repository                $cache
     */
    public function __construct($name, array $config, Container $repository, Repository $cache)
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
     * @param  string   $key
     * @param  mixed    $value
     * @param  bool     $isNew
     * @return bool
     */
    protected function save($key, $value, $isNew = false)
    {
        $model = $this->resolver()->where('name', '=', $key)->first();

        if (true === $isNew && is_null($model)) {
            $this->resolver()->create([
                'name'  => $key,
                'value' => $value,
            ]);
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
        $model = Arr::get($this->config, 'model', $this->name);

        return $this->repository->make($model)->newInstance();
    }
}
