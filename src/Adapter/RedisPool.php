<?php

declare(strict_types=1);

namespace Codin\Stash\Adapter;

use Codin\Stash\Item;
use DateTimeImmutable;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class RedisPool extends AbstractPool implements CacheItemPoolInterface
{
    protected Redis\ConnectionManager $cm;

    /**
     * @param Redis\ConnectionManager $redis
     * @param array<CacheItemInterface> $deferred
     */
    public function __construct(Redis\ConnectionManager $cm, array $deferred = [])
    {
        $this->cm = $cm;
        $this->deferred = $deferred;
    }

    /**
     * @param string $key
     * @return CacheItemInterface
     */
    public function getItem($key)
    {
        $value = $this->cm->getConnection()->get($key);

        if (false === $value) {
            return $this->createItem($key, null);
        }

        $ttl = $this->cm->getConnection()->ttl($key);
        $expires = $ttl ? new DateTimeImmutable('now +'.$ttl.' seconds') : null;

        return $this->createItem($key, $value, true, $expires);
    }

    /**
     * @param array<string> $keys
     * @return array<CacheItemInterface>
     */
    public function getItems(array $keys = [])
    {
        if (!\count($keys)) {
            $keys = $this->cm->getConnection()->keys('*');
        }

        $values = $this->cm->getConnection()->mGet($keys);

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
     * @return bool
     */
    public function clear()
    {
        $this->cm->getConnection()->flushAll();

        return true;
    }

    /**
     * @return bool
     */
    public function deleteItem($key)
    {
        return $this->deleteItems([$key]);
    }

    /**
     * @return bool
     */
    public function deleteItems(array $keys)
    {
        $this->cm->getConnection()->del($keys);

        return true;
    }

    /**
     * @return bool
     */
    public function save(CacheItemInterface $item)
    {
        $this->cm->getConnection()->set($item->getKey(), $item->get());

        if ($item instanceof Item && $expires = $item->getExpires()) {
            $this->cm->getConnection()->expireAt($item->getKey(), (int) $expires->format('U'));
        }

        return true;
    }
}
