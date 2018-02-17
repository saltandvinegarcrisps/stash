<?php

namespace Stash;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class FilesystemPool implements CacheItemPoolInterface
{
    const EXTENSION = 'cache';

    /**
     * @var array
     */
    protected $map = [];

    /**
     * @var string
     */
    protected $path;

    /**
     * Constructor.
     */
    public function __construct(string $path, array $deferred = [])
    {
        if (is_file($path)) {
            throw new \InvalidArgumentException('Cache path argument is a file');
        }

        if (!is_dir($path)) {
            mkdir($path, 0700, true);
        }

        chmod($path, 0700);

        $this->path = $path;
        $this->deferred = $deferred;
    }

    /**
     * Create a new stash item.
     */
    protected function createItem(string $key, $value): CacheItemInterface
    {
        return new Item($key, $value, true);
    }

    /**
     * Get file path from key hash.
     */
    protected function getFilepath(string $key): string
    {
        $hash = hash('sha256', $key);

        return sprintf('%s/%s.%s', $this->path, $hash, self::EXTENSION);
    }

    /**
     * Get hash from file path.
     */
    protected function getPathHash(string $path): string
    {
        return basename($path, '.'.self::EXTENSION);
    }

    /**
     * Get all keys.
     */
    protected function getKeys(): array
    {
        $fi = new \FilesystemIterator($this->path, \FilesystemIterator::SKIP_DOTS);
        $keys = [];

        foreach ($fi as $fileInfo) {
            if ($fileInfo->isFile()) {
                $keys[] = $this->getData($fileInfo->getPathname())['key'];
            }
        }

        return $keys;
    }

    /**
     * Get cache file data.
     */
    protected function getData(string $path): array
    {
        $index = $this->getPathHash($path);

        if (isset($this->map[$index])) {
            return $this->map[$index];
        }

        if (is_file($path)) {
            $contents = file_get_contents($path);

            return $this->map[$index] = json_decode($contents, true);
        }

        return [
            'key' => null,
            'value' => null,
            'expires' => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        $path = $this->getFilepath($key);

        $data = $this->getData($path);

        if ($data['key']) {
            return $this->createItem(
                $data['key'],
                $data['value'],
                true,
                $data['expires'] ? new \DateTime($data['expires']) : null
            );
        }

        return $this->createItem($key, null);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        if (empty($keys)) {
            $keys = $this->getKeys();
        }

        $values = [];

        foreach ($keys as $index => $key) {
            $values[$index] = $this->getItem($key);
        }

        return $values;
    }

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
    public function clear()
    {
        $keys = $this->getKeys();

        $this->deleteItems($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        $path = $this->getFilepath($key);

        if (is_file($path)) {
            unlink($path);
        }

        // clear memory map
        $index = $this->getPathHash($path);
        unset($this->map[$index]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        $expires = $item->getExpires();

        $contents = json_encode([
            'key' => $item->getKey(),
            'value' => $item->get(),
            'expires' => $expires ? $expires->format('Y-m-d H:i:s') : null,
        ]);

        file_put_contents($path, $contents);
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
