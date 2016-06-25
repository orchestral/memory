<?php

namespace Orchestra\Memory;

use PDOException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Support\Arrayable;
use Orchestra\Contracts\Memory\Handler as HandlerContract;

abstract class DatabaseHandler extends Handler implements HandlerContract
{
    /**
     * Load the data from database.
     *
     * @return array
     */
    public function initiate()
    {
        $items    = [];
        $memories = $this->cache instanceof Repository ? $this->getItemsFromCache() : $this->getItemsFromDatabase();

        foreach ($this->asArray($memories) as $memory) {
            $value = $memory->value;

            $items = Arr::add($items, $memory->name, unserialize($value));

            $this->addKey($memory->name, [
                'id'    => $memory->id,
                'value' => $value,
            ]);
        }

        return $items;
    }

    /**
     * Save data to database.
     *
     * @param  array   $items
     *
     * @return bool
     */
    public function finish(array $items = [])
    {
        $changed = false;

        foreach ($items as $key => $value) {
            $serialized = serialize($value);

            if (! $this->check($key, $serialized)) {
                $changed = true;

                try {
                    $this->saving($key, $serialized, $this->isNewKey($key));
                } catch (PDOException $e) {
                    // Should be able to ignore failure since it is possible that
                    // the request is done on a read only connection.
                }
            }
        }

        if ($changed && $this->cache instanceof Repository) {
            $this->cache->forget($this->cacheKey);
        }

        return true;
    }

    /**
     * Attempt to save or remove data to the database.
     *
     * @param  string   $key
     * @param  mixed    $value
     * @param  bool     $isNew
     *
     * @return void
     */
    protected function saving($key, $value, $isNew)
    {
        if ($value === 'N;') {
            $this->delete($key);
        } else {
            $this->save($key, $value, $isNew);
        }
    }

    /**
     * Create/insert data to database.
     *
     * @param  string   $key
     * @param  mixed    $value
     * @param  bool     $isNew
     *
     * @return void
     */
    abstract protected function save($key, $value, $isNew = false);

    /**
     * Remove data from database.
     *
     * @param  string   $key
     *
     * @return void
     */
    abstract protected function delete($key);

    /**
     * Get resolver instance.
     *
     * @return object
     */
    abstract protected function resolver();

    /**
     * Get items from cache.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getItemsFromCache()
    {
        return $this->cache->rememberForever($this->cacheKey, function () {
            return $this->getItemsFromDatabase();
        });
    }

    /**
     * Get items from database.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getItemsFromDatabase()
    {
        return $this->resolver()->get();
    }

    /**
     * Convert mixed value fetch from database to array.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Contracts\Support\Arrayable|array  $data
     *
     * @return array
     */
    protected function asArray($data = [])
    {
        if ($data instanceof Collection) {
            $data = $data->all();
        } elseif ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        return $data;
    }
}
