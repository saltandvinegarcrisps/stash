<?php

namespace Stash;

use Psr\Cache\CacheItemInterface;

abstract class AbstractPool
{
    /**
     * @var array
     */
    protected $deferred;

    /**
     * Create a new stash item.
     */
    protected function createItem(
        string $key,
        $value,
        bool $hit = false,
        \DateTimeInterface $expires = null
    ): Item {
        return new Item($key, $value, $hit, $expires);
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getItem($key);

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        return $this->getItem($key)->isHit();
    }

    /**
     * {@inheritdoc}
     */
    abstract public function deleteItem(string $key);

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys): void
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferred[] = $item;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function save(CacheItemInterface $item): bool;

    /**
     * {@inheritdoc}
     */
    public function commit(): void
    {
        while (!empty($this->deferred)) {
            $this->save(\array_pop($this->deferred));
        }
    }

    /**
     * Commit deferred items
     *
     * @return void
     */
    public function __deconstuct(): void
    {
        $this->commit();
    }

    /**
     * Get expiry datetime from cache item
     *
     * @param CacheItemInterface $item
     * @return null|\DateTimeInterface
     */
    protected function expiresAt(CacheItemInterface $item): ?\DateTimeInterface
    {
        return $item instanceof Item ? $item->getExpires() : null;
    }
}
