<?php

namespace spec\Stash;

use Stash\RedisPool;
use Stash\Item;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RedisPoolSpec extends ObjectBehavior
{
    public function let(\Redis $redis)
    {
        $this->beConstructedWith($redis);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RedisPool::class);
    }

    public function it_should_get_a_item_by_key()
    {
        $this->getItem('foo')->shouldBeAnInstanceOf(Item::class);
    }
}
