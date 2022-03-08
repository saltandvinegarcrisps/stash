<?php

declare(strict_types=1);

namespace Codin\Stash\Adapter\FileSystem;

use Psr\Cache\CacheItemInterface;

interface Serialiser
{
    public function encode(CacheItemInterface $data): string;

    public function decode(string $data): CacheItemInterface;
}
