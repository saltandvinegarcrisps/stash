<?php

namespace Stash;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class RedisPool extends AbstractPool implements CacheItemPoolInterface
{
    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * Constructor.
     */
    public function __construct(\Redis $redis, array $deferred = [])
    {
        $this->redis = $redis;
        $this->deferred = $deferred;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        $value = $this->redis->get($key);

        if (false === $value) {
            return $this->createItem($key, null);
        }

        $ttl = $this->redis->ttl($key);
        $expires = $ttl ? new \DateTime('now +'.$ttl.' seconds') : null;

        return $this->createItem($key, $value, true, $expires);
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
            if (false === $values[$index]) {
                $values[$index] = $this->createItem($key, null);
            } else {
                $values[$index] = $this->createItem($key, $values[$index], true);
            }
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->redis->flushAll();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key): void
    {
        $this->deleteItems([$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys): void
    {
        $this->redis->delete($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item): bool
    {
        $expires = $this->expiresAt($item);

        $this->redis->set($item->getKey(), $item->get());

        if ($expires) {
            return $this->redis->expireAt($item->getKey(), (int) $expires->format('U'));
        } else {
            return $this->redis->persist($item->getKey());
        }
    }
}
