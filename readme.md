# PSR6 Cache Implementation

Example

	use Stash\{
		Item,
		RedisPool
	};

	$redis = new Redis;
	$pool = new RedisPool($redis);

	$item = new Item('my-key', 'some data');
	$item->expiresAfter(3600); // 1 hour

	$pool->save($item);

	$item = $pool->getItem('my-key');
	echo $item->get(); // some data
	echo $item->isHit(); // true
