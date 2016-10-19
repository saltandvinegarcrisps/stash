<?php

namespace spec\Stash;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ItemSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('foo', 'bar');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Stash\Item');
    }

    public function it_should_return_a_key()
    {
        $this->getKey()->shouldBe('foo');
    }

    public function it_should_return_a_value()
    {
        $this->get()->shouldBe('bar');
    }

    public function it_should_set_a_value()
    {
        $this->set('baz');
        $this->get()->shouldBe('baz');
    }
}
