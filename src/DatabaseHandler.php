<?php

namespace Orchestra\Memory;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Orchestra\Contracts\Memory\Handler as HandlerContract;
use PDOException;

abstract class DatabaseHandler extends Handler implements HandlerContract
{
    /**
     * Load the data from database.
     */
    public function initiate(): array
    {
        $items = [];
        $memories = $this->cache instanceof Repository ? $this->getItemsFromCache() : $this->getItemsFromDatabase();

        foreach ($this->asArray($memories) as $memory) {
            $value = $memory->value;

            $items = Arr::add($items, $memory->name, \unserialize($value, ['allowed_classes' => false]));

            $this->addKey($memory->name, [
                'id' => $memory->id,
                'value' => $value,
            ]);
        }

        return $items;
    }

    /**
     * Save data to database.
     */
    public function finish(array $items = []): bool
    {
        $changed = false;

        foreach ($items as $key => $value) {
            $serialized = \serialize($value);

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
     * @param  mixed  $value
     */
    protected function saving(string $key, $value, bool $isNew): void
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
     * @param  mixed  $value
     */
    abstract protected function save(string $key, $value, bool $isNew = false): void;

    /**
     * Remove data from database.
     */
    abstract protected function delete(string $key): void;

    /**
     * Get resolver instance.
     *
     * @return object
     */
    abstract protected function resolver();

    /**
     * Get items from cache.
     */
    protected function getItemsFromCache(): Collection
    {
        return $this->cache->rememberForever($this->cacheKey, function () {
            return $this->getItemsFromDatabase();
        });
    }

    /**
     * Get items from database.
     */
    protected function getItemsFromDatabase(): Collection
    {
        return $this->resolver()->get();
    }

    /**
     * Convert mixed value fetch from database to array.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Contracts\Support\Arrayable|array  $data
     */
    protected function asArray($data = []): array
    {
        if ($data instanceof Collection) {
            $data = $data->all();
        } elseif ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        return $data;
    }
}
