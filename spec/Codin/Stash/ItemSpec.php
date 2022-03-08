<?php

declare(strict_types=1);

namespace spec\Codin\Stash;

use Codin\Stash\Item;
use PhpSpec\ObjectBehavior;

class ItemSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('foo', 'bar', true);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Item::class);
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

    public function it_should_return_hit_value()
    {
        $this->isHit()->shouldBe(true);
    }

    public function it_should_check_expire_aginst_a_datetime_object()
    {
        $this->hasExpired(new \DateTime)->shouldBe(false);
    }

    public function it_should_return_expire_value()
    {
        $this->getExpires()->shouldBe(null);
    }

    public function it_should_set_a_expire_date()
    {
        $now = new \DateTime;
        $this->expiresAt($now);
        $this->getExpires()->format('U')->shouldBe($now->format('U'));
    }

    public function it_should_set_a_expire_null()
    {
        $this->expiresAt(null);
        $this->getExpires()->shouldBe(null);
    }

    public function it_should_set_a_expire_after_null()
    {
        $this->expiresAfter(null);
        $this->getExpires()->shouldBe(null);
    }

    public function it_should_expire_after_a_set_interval()
    {
        $time = new \DateInterval('PT1H');
        $this->expiresAfter($time);

        $now = new \DateTime;
        $this->hasExpired($now)->shouldBe(false);

        $future = new \DateTime('now +1 day');
        $this->hasExpired($future)->shouldBe(true);
    }

    public function it_should_expire_after_a_time_in_seconds()
    {
        $this->expiresAfter(3600);

        $now = new \DateTime;
        $this->hasExpired($now)->shouldBe(false);

        $future = new \DateTime('now +1 day');
        $this->hasExpired($future)->shouldBe(true);
    }
}
