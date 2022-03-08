<?php

declare(strict_types=1);

namespace Codin\Stash\Adapter;

use Codin\Stash\Item;
use FilesystemIterator;
use GlobIterator;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use SplFileInfo;

class FileSystemPool extends AbstractPool implements CacheItemPoolInterface
{
    protected string $path;

    protected string $extension;

    protected FileSystem\IO $io;

    protected FileSystem\Serialiser $serialiser;

    /**
     * @param string $path
     * @param string $extension
     * @param array<CacheItemInterface> $deferred
     * @param FileSystem\IO|null $io
     * @param FileSystem\Serialiser|null $serialiser
     */
    public function __construct(
        string $path,
        string $extension = 'cache',
        array $deferred = [],
        ?FileSystem\IO $io = null,
        ?FileSystem\Serialiser $serialiser = null
    ) {
        $this->path = $path;
        $this->extension = $extension;
        $this->deferred = $deferred;
        $this->io = $io ?? new FileSystem\IO();
        $this->serialiser = $serialiser ?? new FileSystem\PhpSerialiser();

        $this->io->createDir($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        $path = $this->getFilepath($key);

        $item = $this->getData($path);

        if (!$item instanceof Item || $item->hasExpired()) {
            return $this->createItem($key, null);
        }

        return $item;
    }

    /**
     * @param array<string> $keys
     * @return array<CacheItemInterface>
     */
    public function getItems(array $keys = [])
    {
        if (!\count($keys)) {
            $keys = $this->getKeys();
        }

        $values = [];

        foreach ($keys as $index => $key) {
            $values[$index] = $this->getItem($key);
        }

        return $values;
    }

    /**
     * @return bool
     */
    public function deleteItem($key)
    {
        $filepath = $this->getFilepath($key);

        $this->io->deleteFile($filepath);

        return true;
    }

    /**
     * @return bool
     */
    public function clear()
    {
        $keys = $this->getKeys();

        $this->deleteItems($keys);

        return true;
    }

    /**
     * @return bool
     */
    public function save(CacheItemInterface $item)
    {
        $contents = $this->serialiser->encode($item);

        $filepath = $this->getFilepath($item->getKey());

        return $this->io->writeFile($filepath, $contents) > 0;
    }

    /**
     * Get file path from key hash.
     */
    protected function getFilepath(string $key): string
    {
        $hash = \hash('sha256', $key);

        return \sprintf('%s/%s.%s', $this->path, $hash, $this->extension);
    }

    /**
     * @return array<string>
     */
    protected function getKeys(): array
    {
        $it = new GlobIterator(\sprintf('%s/*.%s', $this->path, $this->extension), FilesystemIterator::SKIP_DOTS);
        $keys = [];

        foreach ($it as $fileInfo) {
            $path = $fileInfo instanceof SplFileInfo ? $fileInfo->getPathname() : $fileInfo;
            if ($item = $this->getData($path)) {
                $keys[] = $item->getKey();
            }
        }

        return $keys;
    }

    protected function getData(string $filepath): ?CacheItemInterface
    {
        if ($contents = $this->io->readFile($filepath)) {
            return $this->serialiser->decode($contents);
        }

        return null;
    }
}
