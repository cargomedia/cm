<?php

class CM_Cache_CacheTest extends CMTest_TestCase {

	public function testRPop() {
		$redis = new CM_Cache_Redis();
		$redis->lPush('foo', 'bar');
		$this->assertSame('bar', $redis->rPop('foo'));
		$this->assertNull($redis->rPop('foo'));
	}
}
