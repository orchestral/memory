<?php

namespace Orchestra\Memory;

use Illuminate\Support\Arr;

abstract class Handler
{
    /**
     * Memory name.
     *
     * @var string
     */
    protected $name;

    /**
     * Storage name.
     *
     * @var string
     */
    protected $storage;

    /**
     * Repository instance.
     *
     * @var object
     */
    protected $repository;

    /**
     * Cache instance.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * Cache key.
     *
     * @var string
     */
    protected $cacheKey;

    /**
     * Memory configuration.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Cached key value map with md5 checksum.
     *
     * @var array
     */
    protected $keyMap = [];

    /**
     * Setup a new memory handler.
     */
    public function __construct(string $name, array $config)
    {
        $this->name = $name;
        $this->config = \array_merge($this->config, $config);

        $this->cacheKey = "db-memory:{$this->storage}-{$name}";
    }

    /**
     * Add key with id and checksum.
     */
    protected function addKey(string $name, array $option): void
    {
        $option['checksum'] = $this->generateNewChecksum($option['value']);
        unset($option['value']);

        $this->keyMap = Arr::add($this->keyMap, $name, $option);
    }

    /**
     * Verify checksum.
     */
    protected function check(string $name, string $check = ''): bool
    {
        return Arr::get($this->keyMap, "{$name}.checksum") === $this->generateNewChecksum($check);
    }

    /**
     * Generate a checksum from given value.
     *
     * @param  mixed  $value
     */
    protected function generateNewChecksum($value): string
    {
        if (! \is_string($value)) {
            $value = \is_object($value) ? \spl_object_hash($value) : \serialize($value);
        }

        return \md5($value);
    }

    /**
     * Is given key a new content.
     *
     * @return int|null
     */
    protected function getKeyId(string $name)
    {
        return Arr::get($this->keyMap, "{$name}.id");
    }

    /**
     * Get storage name.
     */
    public function getStorageName(): string
    {
        return $this->storage;
    }

    /**
     * Get handler name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get if from content is new.
     */
    protected function isNewKey(string $name): bool
    {
        return \is_null($this->getKeyId($name));
    }
}
