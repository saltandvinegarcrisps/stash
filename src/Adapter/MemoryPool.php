<?php

declare(strict_types=1);

namespace Codin\Stash\Adapter;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class MemoryPool extends AbstractPool implements CacheItemPoolInterface
{
    /**
     * @var array<CacheItemInterface>
     */
    protected array $pool;

    /**
     * @param array<CacheItemInterface> $pool
     * @param array<CacheItemInterface> $deferred
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
     * @param array<string> $keys
     * @return array<CacheItemInterface>
     */
    public function getItems(array $keys = [])
    {
        if (!\count($keys)) {
            $keys = \array_keys($this->pool);
        }

        $values = [];

        foreach ($keys as $key) {
            $values[] = $this->getItem($key);
        }

        return $values;
    }

    /**
     * @return bool
     */
    public function deleteItem($key)
    {
        unset($this->pool[$key]);

        return true;
    }

    /**
     * @return bool
     */
    public function clear()
    {
        $this->pool = [];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        $this->pool[$item->getKey()] = $item;

        return true;
    }
}
