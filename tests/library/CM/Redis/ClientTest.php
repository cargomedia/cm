<?php

class CM_Redis_ClientTest extends CMTest_TestCase {

	public function tearDown() {
		CM_Redis_Client::getInstance()->flush();
	}

	public function testGetSet() {
		$redis = CM_Redis_Client::getInstance();
		$this->assertSame(false, $redis->get('foo'));

		$redis->set('foo', 'bar');
		$this->assertSame('bar', $redis->get('foo'));
	}

	public function testExists() {
		$redis = CM_Redis_Client::getInstance();
		$this->assertFalse($redis->exists('foo'));

		$redis->set('foo', 'bar');
		$this->assertTrue($redis->exists('foo'));
	}

	public function testRPop() {
		$redis = CM_Redis_Client::getInstance();
		$key = 'foo';
		$redis->lPush($key, 'bar');
		$this->assertSame('bar', $redis->rPop('foo'));
		$this->assertNull($redis->rPop('foo'));
	}

	public function testLLen() {
		$redis = new CM_Redis_Client();
		$this->assertSame(0, $redis->lLen('foo'));

		$redis->lPush('foo', 'bar1');
		$redis->lPush('foo', 'bar2');
		$this->assertSame(2, $redis->lLen('foo'));
	}

	/**
	 * @expectedException CM_Exception_Invalid
	 * @expectedExceptionMessage does not contain a list
	 */
	public function testLLenNotList() {
		$redis = new CM_Redis_Client();
		$redis->zAdd('foo', 2, 'bar');
		$redis->lLen('foo');
	}

	public function testLTrim() {
		$redis = new CM_Redis_Client();
		$redis->lPush('foo', 'bar1');
		$redis->lPush('foo', 'bar2');
		$redis->lPush('foo', 'bar3');

		$redis->lTrim('foo', 1, 1);
		$this->assertSame(array('bar2'), $redis->lRange('foo'));
	}

	public function testLRange() {
		$redis = new CM_Redis_Client();

		$redis->rPush('foo', 'bar1');
		$redis->rPush('foo', 'bar2');
		$redis->rPush('foo', 'bar3');
		$this->assertSame(array('bar1', 'bar2', 'bar3'), $redis->lRange('foo'));
		$this->assertSame(array('bar2', 'bar3'), $redis->lRange('foo', 1));
		$this->assertSame(array('bar2'), $redis->lRange('foo', 1, 1));
	}

	public function testZRangeByScore() {
		$redis = new CM_Redis_Client();
		$key = 'foo';
		$redis->zAdd($key, 1, 'foo');
		$redis->zAdd($key, 1.5, 'bar');
		$redis->zAdd($key, 2, 'foobar');
		// normal behaviour
		$this->assertSame(array('foo', 'bar', 'foobar'), $redis->zRangeByScore($key, 1, 2));
		// count
		$this->assertSame(array('foo', 'bar'), $redis->zRangeByScore($key, 1, 2, 2));
		// offset
		$this->assertSame(array('bar', 'foobar'), $redis->zRangeByScore($key, 1, 2, null, 1));
		// withscores
		$this->assertSame(array('foo' => '1', 'bar' => '1.5', 'foobar' => '2'), $redis->zRangeByScore($key, 1, 2, null, null, true));
		$this->assertSame(array(), $redis->zRangeByScore($key, 1, 2, 0, 0));
	}
}
