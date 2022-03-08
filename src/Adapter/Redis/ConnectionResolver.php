<?php

declare(strict_types=1);

namespace Codin\Stash\Adapter\Redis;

use Redis;

/**
 * Lazy load Redis connection
 */
class ConnectionResolver implements ConnectionManager
{
    /**
     * @var callable
     */
    protected $resolver;

    public function __construct(callable $resolver)
    {
        $this->resolver = $resolver;
    }

    public function getConnection(): Redis
    {
        return ($this->resolver)();
    }
}
