<?php

namespace Stash;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class MemoryPool implements CacheItemPoolInterface
{
    protected $pool;

    protected $deferred;

    public function __construct(array $pool = [], array $deferred = [])
    {
        $this->pool = $pool;
        $this->deferred = $deferred;
    }

    public function getItem($key)
    {
        return $this->pool[$key];
    }

    public function getItems(array $keys = [])
    {
        if (empty($keys)) {
            return $this->pool;
        }
        return array_intersect_key($this->pool, array_fill_keys($keys, null));
    }

    public function hasItem($key)
    {
        return array_key_exists($key, $this->pool);
    }

    public function clear()
    {
        $this->pool = [];
    }

    public function deleteItem($key)
    {
        unset($this->pool[$key]);
    }

    public function deleteItems(array $keys)
    {
        $this->pool = array_diff_key($this->pool, array_fill_keys($keys, null));
    }

    public function save(CacheItemInterface $item)
    {
        $this->pool[$item->getKey()] = $item;
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferred[$item->getKey()] = $item;
    }

    public function commit()
    {
        $this->pool = array_merge($this->pool, $this->deferred);
    }
}
