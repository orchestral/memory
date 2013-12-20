<?php namespace Orchestra\Memory;

use Illuminate\Cache\CacheManager;
use Illuminate\Database\DatabaseManager;
use Orchestra\Support\Str;

class FluentMemoryHandler extends Abstractable\Handler implements MemoryHandlerInterface
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
     * Load the data from database using Fluent Query Builder.
     *
     * @return void
     */
    public function initiate()
    {
        $items = array();
        $query = $this->getTable();

        if ($this->cache instanceof CacheManager) {
            $query->remember(60, $this->cacheKey);
        }

        $memories = $query->get();

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
     * Add a finish event using Fluent Query Builder.
     *
     * @param  array   $items
     * @return boolean
     */
    public function finish(array $items = array())
    {
        $changed = false;

        foreach ($items as $key => $value) {
            $isNew = $this->isNewKey($key);
            $id    = $this->getKeyId($key);

            $serializedValue = serialize($value);

            if ($this->check($key, $serializedValue)) {
                continue;
            }

            $changed = true;

            $count = $this->getTable()->where('name', '=', $key)->count();

            if (true === $isNew and $count < 1) {
                $this->getTable()->insert(array(
                    'name'  => $key,
                    'value' => $serializedValue,
                ));
            } else {
                $this->getTable()->where('id', '=', $id)->update(array(
                    'value' => $serializedValue,
                ));
            }
        }

        if ($changed and $this->cache instanceof CacheManager) {
            $this->cache->forget($this->cacheKey);
        }

        return true;
    }

     /**
     * Get model instance.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getTable()
    {
        $table = array_get($this->config, 'table', $this->name);

        return $this->repository->table($table);
    }
}
