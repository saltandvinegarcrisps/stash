<?php

namespace Stash;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class MemoryPool extends AbstractPool implements CacheItemPoolInterface
{
    /**
     * @var array
     */
    protected $pool;

    /**
     * Constructor.
     */
    public function __construct(array $pool = [], array $deferred = [])
    {
        $this->pool = $pool;
        $this->deferred = $deferred;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        if (\array_key_exists($key, $this->pool)) {
            return $this->pool[$key];
        }

        return $this->createItem($key, null);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        if (empty($keys)) {
            $keys = \array_keys($this->pool);
        }

        $values = [];

        foreach ($keys as $key) {
            $values[] = $this->getItem($key);
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key): void
    {
        unset($this->pool[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->pool = [];
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item): bool
    {
        $this->pool[$item->getKey()] = $item;

        return true;
    }
}
