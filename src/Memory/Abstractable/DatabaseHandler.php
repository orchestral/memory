<?php namespace Orchestra\Memory\Abstractable;

use Orchestra\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Cache\Repository;
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

        foreach ($memories as $memory) {
            $value = Str::streamGetContents($memory->value);

            $items = Arr::add($items, $memory->name, unserialize($value));

            $this->addKey($memory->name, array(
                'id'    => $memory->id,
                'value' => $value,
            ));
        }

        return $items;
    }

    /**
     * Save data to database.
     *
     * @param  array   $items
     * @return bool
     */
    public function finish(array $items = array())
    {
        $changed = false;

        foreach ($items as $key => $value) {
            $isNew = $this->isNewKey($key);
            $value = serialize($value);

            if (! $this->check($key, $value)) {
                $changed = true;

                $this->save($key, $value, $isNew);
            }
        }

        if ($changed && $this->cache instanceof Repository) {
            $this->cache->forget($this->cacheKey);
        }

        return true;
    }

    /**
     * Create/insert data to database.
     *
     * @param  string   $key
     * @param  mixed    $value
     * @param  bool     $isNew
     * @return bool
     */
    abstract protected function save($key, $value, $isNew = false);

    /**
     * Get resolver instance.
     *
     * @return object
     */
    abstract protected function resolver();

    /**
     * Get items from cache.
     *
     * @return array
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
     * @return array
     */
    protected function getItemsFromDatabase()
    {
        return $this->resolver()->get();
    }
}
