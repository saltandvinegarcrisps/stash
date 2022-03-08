<?php

declare(strict_types=1);

namespace Codin\Stash\Adapter;

use Codin\Stash\Item;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

abstract class AbstractPool
{
    /**
     * @var array<CacheItemInterface>
     */
    protected array $deferred;

    /**
     * @param string $key
     * @param mixed $value
     * @param bool $hit
     * @param DateTimeInterface|null $expires
     */
    protected function createItem(
        string $key,
        $value,
        bool $hit = false,
        DateTimeInterface $expires = null
    ): Item {
        return new Item($key, $value, $hit, $expires);
    }

    /**
     * @param string $key
     * @return CacheItemInterface
     */
    abstract public function getItem($key);

    /**
     * @param string $key
     * @return bool
     */
    public function hasItem($key)
    {
        return $this->getItem($key)->isHit();
    }

    /**
     * @param string $key
     * @return bool
     */
    abstract public function deleteItem(string $key);

    /**
     * @param array<string> $keys
     * @return bool
     */
    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferred[] = $item;

        return true;
    }

    /**
     * @return bool
     */
    abstract public function save(CacheItemInterface $item);

    /**
     * @return bool
     */
    public function commit()
    {
        while (\count($this->deferred)) {
            $this->save(\array_pop($this->deferred));
        }

        return true;
    }

    /**
     * Commit deferred items
     */
    public function __deconstuct(): void
    {
        $this->commit();
    }
}
