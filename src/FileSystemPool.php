<?php

namespace Stash;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class FileSystemPool extends AbstractPool implements CacheItemPoolInterface
{
    use FileSystemTrait;

    const EXTENSION = 'cache';

    /**
     * @var string
     */
    protected $path;

    /**
     * Constructor.
     */
    public function __construct(string $path, array $deferred = [])
    {
        if (\is_file($path)) {
            throw new \InvalidArgumentException('Cache path argument is a file');
        }

        if (!\is_dir($path)) {
            \mkdir($path, 0700, true);
        }

        \chmod($path, 0700);

        $this->path = $path;
        $this->deferred = $deferred;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        $path = $this->getFilepath($key);

        $data = $this->getData($path);

        if ($data['key']) {
            // has expiry date
            if ($data['expires']) {
                $now = new \DateTime;
                $expires = new \DateTime($data['expires']);

                // not expired
                if ($expires > $now) {
                    return $this->createItem($data['key'], $data['value'], true, $expires);
                } else {
                    return $this->createItem($data['key'], null);
                }
            }

            return $this->createItem($data['key'], $data['value'], true, null);
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
    public function deleteItem($key): void
    {
        $path = $this->getFilepath($key);

        if (\is_file($path)) {
            \unlink($path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $keys = $this->getKeys();

        $this->deleteItems($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item): bool
    {
        $expires = $this->expiresAt($item);

        $contents = \json_encode([
            'key' => $item->getKey(),
            'value' => $item->get(),
            'expires' => $expires ? $expires->format('Y-m-d H:i:s') : null,
        ]);

        $path = $this->getFilepath($item->getKey());

        return \file_put_contents($path, $contents) > 0;
    }
}
