<?php

declare(strict_types=1);

namespace Codin\Stash\Adapter\Redis;

use Redis;

interface ConnectionManager
{
    public function getConnection(): Redis;
}
