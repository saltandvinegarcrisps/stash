<?php

namespace Stash;

trait FileSystemTrait
{
    /**
     * Get file path from key hash.
     */
    protected function getFilepath(string $key): string
    {
        $hash = \hash('sha256', $key);

        return \sprintf('%s/%s.%s', $this->path, $hash, self::EXTENSION);
    }

    /**
     * Get all keys.
     */
    protected function getKeys(): array
    {
        $it = new \GlobIterator(\sprintf('%s/*.%s', $this->path, self::EXTENSION), \FilesystemIterator::SKIP_DOTS);
        $keys = [];

        foreach ($it as $fileInfo) {
            $path = $fileInfo instanceof \SplFileInfo ? $fileInfo->getPathname() : $fileInfo;
            $item = $this->getData($path);
            $keys[] = $item['key'];
        }

        return $keys;
    }

    /**
     * Get cache file data.
     */
    protected function getData(string $path): array
    {
        if (\is_file($path)) {
            $contents = \file_get_contents($path);
            if (false === $contents) {
                throw new \ErrorException('Failed to read cache file: '.$path);
            }
            return \json_decode($contents, true);
        }

        return [
            'key' => null,
            'value' => null,
            'expires' => null,
        ];
    }
}
