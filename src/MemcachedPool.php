<?php

namespace Stash;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class MemcachedPool implements CacheItemPoolInterface
{
    /**
     * @var object
     */
    protected $memcached;

    /**
     * @var array
     */
    protected $deferred;

    /**
     * Constructor.
     */
    public function __construct(\Memcached $memcached, array $deferred = [])
    {
        $this->memcached = $memcached;
        $this->deferred = $deferred;
    }

    /**
     * Create a new stash item.
     */
    protected function createItem(string $key, $value): CacheItemInterface
    {
        if (false === $value) {
            return new Item($key, null, false);
        }

        return new Item($key, $value, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        $value = $this->memcached->get($key);

        return $this->createItem($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        if (empty($keys)) {
            $keys = $this->memcached->getAllKeys();
        }

        $values = $this->memcached->getMulti($keys);

        foreach ($keys as $index => $key) {
            $values[$index] = $this->createItem($key, $values[$index]);
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        return $this->memcached->get($key) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->memcached->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        $this->memcached->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        $this->memcached->deleteMulti($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        if ($expires = $item->getExpires()) {
            $now = time();
            $seconds = $expires->format('U') - $now;
            $this->memcached->set($item->getKey(), $item->get(), $seconds);
        } else {
            $this->memcached->set($item->getKey(), $item->get());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferred[] = $item;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        while (!empty($this->deferred)) {
            $this->save(array_pop($this->deferred));
        }
    }
}
