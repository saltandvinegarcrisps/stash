<?php

namespace spec\Stash;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MemoryPoolSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Stash\MemoryPool');
    }

    public function is_should_get_a_item_by_key()
    {
        $item = new \Stash\Item('foo', 'bar');
        $this->beConstructedWith([
            $item->getKey() => $item,
        ]);
        $this->getItem()->shouldBe($item);
    }

    public function is_should_get_all_items()
    {
        $item = new \Stash\Item('foo', 'bar');
        $this->beConstructedWith([
            $item->getKey() => $item,
        ]);
        $this->getItems()->shouldContain($item);
    }

    public function is_should_get_all_items_by_keys()
    {
        $item = new \Stash\Item('foo', 'bar');
        $this->beConstructedWith([
            $item->getKey() => $item,
        ]);
        $this->getItems(['foor'])->shouldContain($item);
    }
}
