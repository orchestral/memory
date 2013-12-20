<?php namespace Orchestra\Memory;

use Illuminate\Container\Container;
use Illuminate\Cache\CacheManager;
use Orchestra\Support\Str;

class EloquentMemoryHandler extends Abstractable\Handler implements MemoryHandlerInterface
{
    /**
     * Storage name.
     *
     * @var string
     */
    protected $storage = 'eloquent';

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
        $this->repository = $repository;
        $this->cache      = $cache;

        parent::__construct($name, $config);
    }

    /**
     * Load the data from database using Eloquent ORM.
     *
     * @return array
     */
    public function initiate()
    {
        $items    = array();
        $memories = $this->getModel()->remember(60, $this->cacheKey)->all();

        foreach ($memories as $memory) {
            $value = Str::streamGetContents($memory->value);

            array_set($items, $memory->name, unserialize($value));

            $this->addKey($memory->name, array(
                'id'    => $memory->id,
                'value' => $value,
            ));
        }

        return $items;
    }

    /**
     * Save data to database using Eloquent ORM.
     *
     * @param  array   $items
     * @return boolean
     */
    public function finish(array $items = array())
    {
        $changed = false;

        foreach ($items as $key => $value) {
            $isNew = $this->isNewKey($key);

            $serializedValue = serialize($value);

            if ($this->check($key, $serializedValue)) {
                continue;
            }

            $changed = true;

            $model = $this->getModel()->where('name', '=', $key)->first();

            if (true === $isNew and is_null($model)) {
                $this->getModel()->create(array(
                    'name'  => $key,
                    'value' => $serializedValue,
                ));
            } else {
                $model->value = $serializedValue;

                $model->save();
            }
        }

        $changed and $this->cache->forget($this->cacheKey);

        return true;
    }

    /**
     * Get model instance.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getModel()
    {
        $name = array_get($this->config, 'model', $this->name);

        return $this->repository->make($name)->newInstance();
    }
}
