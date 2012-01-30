<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_QueueTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
		CM_Cache_Redis::flush();
	}

	public function testConstructor() {
		try {
			$queue = new CM_Queue('');
			$this->fail('No error with empty key');
		} catch(CM_Exception_Invalid $e) {
			$this->assertTrue(true);
		}

		$queue = new CM_Queue('foo');
		$this->assertSame('foo', $queue->getKey());
	}

	public function testPushPop() {
		$queue1 = new CM_Queue('foo');
		$queue2 = new CM_Queue('bar');

		$queue1->push(12);
		$this->assertSame(12, $queue1->pop());
		$this->assertSame(false, $queue1->pop());
		$this->assertSame(false, $queue2->pop());

		$queue2->push(1);
		$queue2->push('two');
		$queue2->push(array(3 => 'three'));
		$this->assertSame(1, $queue2->pop());
		$this->assertSame('two', $queue2->pop());
		$this->assertSame(array(3 => 'three'), $queue2->pop());
		$this->assertSame(false, $queue2->pop());
		$this->assertSame(false, $queue1->pop());

	}
}
