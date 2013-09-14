<?php

class CM_Cache_CacheTest extends CMTest_TestCase {

	public function tearDown() {
		CM_Redis_Client::getInstance()->flush();
	}

	public function testRPop() {
		$redis = CM_Redis_Client::getInstance();
		$key = 'foo';
		$redis->lPush($key, 'bar');
		$this->assertSame('bar', $redis->rPop('foo'));
		$this->assertNull($redis->rPop('foo'));
	}

	public function testZRangeByScore() {
		$redis = new CM_Redis_Client();
		$key = 'foo';
		$redis->zAdd($key, 1, 'foo');
		$redis->zAdd($key, 1.5, 'bar');
		$redis->zAdd($key, 2, 'foobar');
		// normal behaviour
		$this->assertSame(array('foo', 'bar' ,'foobar'), $redis->zRangeByScore($key, 1, 2));
		// count
		$this->assertSame(array('foo', 'bar'), $redis->zRangeByScore($key, 1, 2, 2));
		// offset
		$this->assertSame(array('bar', 'foobar'), $redis->zRangeByScore($key, 1, 2, null, 1));
		// withscores
		$this->assertSame(array('foo' => '1', 'bar' => '1.5', 'foobar' => '2'), $redis->zRangeByScore($key, 1, 2, null, null, true));
		$this->assertSame(array(), $redis->zRangeByScore($key, 1, 2, 0, 0));
	}
}
