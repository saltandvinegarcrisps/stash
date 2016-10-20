<?php

namespace spec\Stash;

use Stash\MemoryPool;
use Stash\Item;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MemoryPoolSpec extends ObjectBehavior
{
    protected function createItem($key, $value, $hit)
    {
        return new Item($key, $value, $hit);
    }

    public function let()
    {
        $a = $this->createItem('foo', '1', true);
        $b = $this->createItem('bar', '2', true);

        $this->beConstructedWith([
            $a->getKey() => $a,
            $b->getKey() => $b,
        ]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MemoryPool::class);
    }

    public function it_should_get_a_item_by_key()
    {
        $this->getItem('foo')->shouldBeAnInstanceOf(Item::class);
        $this->getItem('foo')->isHit()->shouldBe(true);
    }

    public function it_should_get_a_empty_item_with_missing_key()
    {
        $this->getItem('baz')->shouldBeAnInstanceOf(Item::class);
        $this->getItem('baz')->isHit()->shouldBe(false);
    }

    public function it_should_get_all_items()
    {
        $this->getItems()->shouldHaveCount(2);
    }

    public function it_should_get_all_items_by_keys()
    {
        $this->getItems(['foo'])[0]->isHit()->shouldBe(true);
        $this->getItems(['baz'])[0]->isHit()->shouldBe(false);
    }

    public function it_should_check_item_exists_by_key()
    {
        $this->hasItem('foo')->shouldBe(true);
        $this->hasItem('baz')->shouldBe(false);
    }

    public function it_should_clear_all_items()
    {
        $this->clear();
        $this->getItems()->shouldHaveCount(0);
    }

    public function it_should_delete_item_by_key()
    {
        $this->deleteItem('foo');
        $this->hasItem('foo')->shouldBe(false);
    }

    public function it_should_delete_many_by_key()
    {
        $this->deleteItems(['foo', 'bar']);
        $this->getItems()->shouldHaveCount(0);
    }

    public function it_should_save_a_item()
    {
        $item = $this->createItem('baz', '3', true);
        $this->save($item);
        $this->hasItem('baz')->shouldBe(true);
    }

    public function it_should_defer_a_item()
    {
        $this->saveDeferred($this->createItem('qux', '4', true));
        $this->hasItem('qux')->shouldBe(false);
    }

    public function it_should_commit_deferred_a_items()
    {
        $this->saveDeferred($this->createItem('quux', '5', true));
        $this->commit();
        $this->hasItem('quux')->shouldBe(true);
    }
}
