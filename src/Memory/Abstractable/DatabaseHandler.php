<?php namespace Orchestra\Memory\Abstractable;

use Orchestra\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Cache\Repository;
use Orchestra\Contracts\Memory\MemoryHandler;

abstract class DatabaseHandler extends Handler implements MemoryHandler
{
    /**
     * Load the data from database.
     *
     * @return array
     */
    public function initiate()
    {
        $items    = [];
        $cacheKey = $this->cacheKey;

        $memories = $this->cache->rememberForever($cacheKey, function () use ($cacheKey) {
            $result = $this->resolver()->get();

            return $result;
        });

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
}
