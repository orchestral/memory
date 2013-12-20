<?php namespace Orchestra\Memory;

use Illuminate\Cache\Repository;
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
     * Database table name.
     *
     * @var string
     */
    protected $table = null;

    /**
     * Setup a new memory handler.
     *
     * @param  string                                 $name
     * @param  array                                  $config
     * @param  \Illuminate\Database\DatabaseManager   $repository
     * @param  \Illuminate\Cache\Repository           $cache
     */
    public function __construct($name, array $config, DatabaseManager $repository, Repository $cache)
    {
        $this->repository = $repository;
        $this->cache      = $cache;
        $this->table      = array_get($config, 'table', $name);

        parent::__construct($name, $config);
    }

    /**
     * Load the data from database using Fluent Query Builder.
     *
     * @return void
     */
    public function initiate()
    {
        $items    = array();
        $memories = $this->repository->table($this->table)->remember(60, $this->cacheKey)->get();

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

            $count = $this->repository->table($this->table)->where('name', '=', $key)->count();

            if (true === $isNew and $count < 1) {
                $this->repository->table($this->table)->insert(array(
                    'name'  => $key,
                    'value' => $serializedValue,
                ));
            } else {
                $this->repository->table($this->table)->where('id', '=', $id)->update(array(
                    'value' => $serializedValue,
                ));
            }
        }

        $changed and $this->cache->forget($this->cacheKey);

        return true;
    }
}
