<?php

namespace Stash;

use Psr\Cache\CacheItemInterface;

class Item implements CacheItemInterface
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var bool
     */
    protected $hit;

    /**
     * @var \DateTimeInterface|null
     */
    protected $expires;

    /**
     * Constructor.
     */
    public function __construct(string $key, $value, bool $hit = false, \DateTimeInterface $expires = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->hit = $hit;
        $this->expires = $expires;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function isHit()
    {
        return $this->hit;
    }

    /**
     * {@inheritdoc}
     */
    public function set($value): void
    {
        $this->value = $value;
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt($expiration)
    {
        if ($expiration instanceof \DateTimeInterface) {
            $this->expires = $expiration;
        } elseif (\is_null($expiration)) {
            $this->expires = null;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter($time): void
    {
        if ($time instanceof \DateInterval) {
            $this->expires = (new \DateTimeImmutable())->add($time);
        } elseif (\is_int($time)) {
            $this->expires = new \DateTimeImmutable('now +'.$time.' seconds');
        } elseif (\is_null($time)) {
            $this->expires = null;
        }
        
        return $this;
    }

    /**
     * Returns the expiration DateTimeInterface object or null.
     *
     * @return null|\DateTimeInterface
     */
    public function getExpires(): ?\DateTimeInterface
    {
        return $this->expires;
    }
    /**
     * Returns true or false if the item has expired compared to \DateTimeInterface.
     *
     * @param \DateTimeInterface $date
     * @return bool
     */
    public function hasExpired(\DateTimeInterface $date): bool
    {
        return null === $this->expires ? false : $this->expires < $date;
    }
}
