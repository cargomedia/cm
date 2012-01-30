<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_SetTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
		CM_Cache_Redis::flush();
	}

	public function testConstructor() {
		try {
			$queue = new CM_Set('');
			$this->fail('No error with empty key');
		} catch (CM_Exception_Invalid $e) {
			$this->assertTrue(true);
		}

		$queue = new CM_Set('foo');
		$this->assertSame('foo', $queue->getKey());
	}

	public function testAddPopAll() {
		$set1 = new CM_Set('foo');
		$set2 = new CM_Set('bar');

		$set1->add(12);
		$this->assertSame(array(12), $set1->flush());
		$this->assertSame(array(), $set1->flush());
		$this->assertSame(array(), $set2->flush());

		$valuesExpected = array(1, 'two', array(3 => 'three'));
		foreach ($valuesExpected as $valueExpected) {
			$set2->add($valueExpected);
		}
		$this->assertContainsAll($valuesExpected, $set2->flush());
		$this->assertSame(array(), $set2->flush());
		$this->assertSame(array(), $set1->flush());
	}

	public function testDelete() {
		$set = new CM_Set('foo');
		$set->add(1);
		$set->add(2);
		$set->add(3);
		$set->delete(2);
		$values = $set->flush();
		$this->assertContainsAll(array(1, 3), $values);
		$this->assertNotContains(2, $values);
	}
}
