<?php

declare(strict_types=1);

namespace Codin\Stash\Adapter\FileSystem;

use Psr\Cache\CacheItemInterface;

class PhpSerialiser implements Serialiser
{
    public function encode(CacheItemInterface $data): string
    {
        return serialize($data);
    }

    public function decode(string $data): CacheItemInterface
    {
        $object = unserialize($data);

        if (!$object instanceof CacheItemInterface) {
            throw new \RuntimeException('Failed to decode data for unknown object');
        }

        return $object;
    }
}
