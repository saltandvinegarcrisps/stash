<?php

namespace Stash;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class RedisPool implements CacheItemPoolInterface
{
    /**
     * @var array
     */
    protected $redis;

    /**
     * @var array
     */
    protected $deferred;

    /**
     * Constructor
     */
    public function __construct(\Redis $redis, array $deferred = [])
    {
        $this->redis = $redis;
        $this->deferred = $deferred;
    }

    /**
     * Create a new stash item
     */
    protected function createItem(string $key, $value): CacheItemInterface
    {
        return new Item($key, false === $value ? null : $value, false === $value ? false : true);
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        $value = $this->redis->get($key);

        $item = $this->createItem($key, $value);

        if ($item->isHit() && $ttl = $this->redis->ttl($key)) {
            $item->expiresAfter($ttl);
        }

        return $item;
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

            if ($ttl = $this->redis->ttl($key)) {
                $values[$index]->expiresAfter($ttl);
            }
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
            $this->redis->expireAt($item->getKey(), $item->getExpires()->format('U'));
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
        while (! empty($this->deferred)) {
            $this->save(array_pop($this->deferred));
        }
    }
}
