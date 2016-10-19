<?php

namespace Stash;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class MemoryPool implements CacheItemPoolInterface
{
    /**
     * @var array
     */
    protected $pool;

    /**
     * @var array
     */
    protected $deferred;

    /**
     * Constructor
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
        if (! $this->hasItem($key)) {
            $this->pool[$key] = new Item($key, null, false);
        }

        return $this->pool[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        if (empty($keys)) {
            return $this->pool;
        }
        return array_intersect_key($this->pool, array_fill_keys($keys, null));
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        return array_key_exists($key, $this->pool);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->pool = [];
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        $this->deleteItems([$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        $this->pool = array_diff_key($this->pool, array_fill_keys($keys, null));
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        $this->pool[$item->getKey()] = $item;
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferred[$item->getKey()] = $item;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $this->pool = array_merge($this->pool, $this->deferred);
    }
}
