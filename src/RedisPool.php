<?php

namespace Stash;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class RedisPool implements CacheItemPoolInterface
{
    /**
     * @var object
     */
    protected $redis;

    /**
     * @var array
     */
    protected $deferred;

    /**
     * Constructor.
     */
    public function __construct(\Redis $redis, array $deferred = [])
    {
        $this->redis = $redis;
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
        $value = $this->redis->get($key);

        return $this->createItem($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        if (empty($keys)) {
            $keys = $this->redis->keys('*');
        }

        $values = $this->redis->mGet($keys);

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
        return $this->redis->exists($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->redis->flushAll();
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
        $this->redis->delete($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        $this->redis->set($item->getKey(), $item->get());

        if ($expires = $item->getExpires()) {
            $this->redis->expireAt($item->getKey(), $expires->format('U'));
        } else {
            $this->redis->persist($item->getKey());
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
