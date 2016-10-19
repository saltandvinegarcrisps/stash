<?php

namespace Stash;

use Psr\Cache\CacheItemInterface;

class Item implements CacheItemInterface
{
    protected $key;

    protected $value;

    protected $isHit;

    protected $expires;

    public function __construct(string $key, string $value, bool $isHit, \DateTimeInterface $expires = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->isHit = $isHit;
        $this->expires = $expires;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function get()
    {
        return $this->value;
    }

    public function isHit()
    {
        return $this->isHit;
    }

    public function set($value)
    {
        $this->value = $value;
    }

    public function expiresAt($expiration)
    {
    }

    public function expiresAfter($time)
    {
    }
}
