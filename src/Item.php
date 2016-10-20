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
     * @var object|null
     */
    protected $expires;

    /**
     * Constructor
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
    public function set($value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt($expiration)
    {
        if ($expiration instanceof \DateTimeInterface) {
            $this->expires = $expiration;
        } elseif (is_null($expiration)) {
            $this->expires = null;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter($time)
    {
        if ($time instanceof \DateInterval) {
            $expires = new \DateTime();
            $expires->add($time);
            $this->expires = $expires;
        } elseif (is_int($time)) {
            $this->expires = new \DateTime('now +' . $time . ' seconds');
        } elseif (is_null($time)) {
            $this->expires = null;
        }
    }

    /**
     * Returns the expiration DateTime object or null
     *
     * @return mixed null|object DateTime
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Returns true or false if the item has expired compared to argument datetime
     *
     * @param object
     * @return bool
     */
    public function hasExpired(\DateTimeInterface $date)
    {
        return null === $this->expires ? false : $this->expires < $date;
    }
}
