<?php namespace Orchestra\Memory\Abstractable;

use Illuminate\Cache\Repository;
use Illuminate\Support\Arr;
use Orchestra\Memory\MemoryHandlerInterface;
use Orchestra\Support\Str;

abstract class DatabaseHandler extends Handler implements MemoryHandlerInterface
{
    /**
     * Load the data from database.
     *
     * @return array
     */
    public function initiate()
    {
        $items = array();
        $query = $this->resolver();

        if ($this->cache instanceof Repository) {
            $query->remember(60, $this->cacheKey);
        }

        $memories = $query->get();

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
