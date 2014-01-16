<?php namespace Orchestra\Memory\Abstractable;

use Illuminate\Cache\CacheManager;
use Orchestra\Memory\MemoryHandlerInterface;
use Orchestra\Support\Str;

abstract class DatabaseHandler extends Handler implements MemoryHandlerInterface
{
    /**
     * Load the data from database.
     *
     * @return void
     */
    public function initiate()
    {
        $items = array();
        $query = $this->resolver();

        if ($this->cache instanceof CacheManager) {
            $query->remember(60, $this->cacheKey);
        }

        $memories = $query->get();

        foreach ($memories as $memory) {
            $value = Str::streamGetContents($memory->value);
            $value = unserialize($value);

            array_set($items, $memory->name, $value);

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
     * @return boolean
     */
    public function finish(array $items = array())
    {
        $changed = false;

        foreach ($items as $key => $value) {
            $isNew = $this->isNewKey($key);

            if ($this->check($key, $value)) {
                continue;
            }

            $changed = true;

            $this->save($key, serialize($value), $isNew);
        }

        if ($changed and $this->cache instanceof CacheManager) {
            $this->cache->forget($this->cacheKey);
        }

        return true;
    }

    /**
     * Create/insert data to database.
     *
     * @param  array   $items
     * @return boolean
     */
    abstract protected function save($key, $value, $isNew = false);

    /**
     * Get resolver instance.
     *
     * @return object
     */
    abstract protected function resolver();
}
