<?php

declare(strict_types=1);

namespace Codin\Stash;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

class Item implements CacheItemInterface
{
    protected string $key;

    /**
     * @var mixed
     */
    protected $value;

    protected bool $hit;

    protected ?DateTimeInterface $expires;

    /**
     * @param string $key
     * @param mixed $value
     * @param bool $hit
     * @param DateTimeInterface|null $expires
     */
    public function __construct(string $key, $value, bool $hit = false, ?DateTimeInterface $expires = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->hit = $hit;
        $this->expires = $expires;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isHit()
    {
        return $this->hit;
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function set($value)
    {
        $this->value = $value;
        
        return $this;
    }

    /**
     * @param DateTimeInterface|null $expiration
     * @return static
     */
    public function expiresAt($expiration)
    {
        if ($expiration instanceof DateTimeInterface) {
            $this->expires = $expiration;
        } elseif (\is_null($expiration)) {
            $this->expires = null;
        }

        return $this;
    }

    /**
     * @param int|DateInterval|null $time
     * @return static
     */
    public function expiresAfter($time)
    {
        if ($time instanceof DateInterval) {
            $this->expires = (new DateTimeImmutable())->add($time);
        } elseif (\is_int($time)) {
            $this->expires = new DateTimeImmutable('now +'.$time.' seconds');
        } elseif (\is_null($time)) {
            $this->expires = null;
        }
        
        return $this;
    }

    /**
     * Returns the expiration DateTimeInterface object or null.
     *
     * @return null|DateTimeInterface
     */
    public function getExpires(): ?DateTimeInterface
    {
        return $this->expires;
    }
    
    /**
     * Returns true or false if the item has expired compared to \DateTimeInterface.
     *
     * @param DateTimeInterface|null $context
     * @return bool
     */
    public function hasExpired(?DateTimeInterface $context = null): bool
    {
        $date = $context ?? new DateTimeImmutable();

        return null === $this->expires ? false : $this->expires < $date;
    }
}
