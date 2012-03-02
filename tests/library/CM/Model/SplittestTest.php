<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_Model_SplittestTest extends TestCase {

	public function testCreate() {
		$test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
		$this->assertInstanceOf('CM_Model_Splittest', $test);

		try {
			$test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
			$this->fail('Could create duplicate splittest');
		} catch (CM_Exception $e) {
			$this->assertTrue(true);
		}

		$test->delete();
	}

	public function testConstruct() {
		$test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
		$test2 = new CM_Model_Splittest('foo');
		$this->assertModelEquals($test, $test2);

		$test->delete();
	}

	public function testGetId() {
		$test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
		$this->assertGreaterThanOrEqual(1, $test->getId());

		$test->delete();
	}

	public function testGetCreated() {
		$time = time();
		/** @var CM_Model_Splittest $test */
		$test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
		$this->assertGreaterThanOrEqual($time, $test->getCreated());

		$test->delete();
	}

	public function testGetVariations() {
		/** @var CM_Model_Splittest $test */
		$test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
		$this->assertContainsAll(array('v1', 'v2'), $test->getVariations());

		$test->delete();
	}

	public function testGetVariationFixture() {
		$user1 = TH::createUser();
		/** @var CM_Model_Splittest $test */
		$test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));

		$variationUser1 = $test->getVariationFixture($user1);
		$this->assertContains($variationUser1, array('v1', 'v2'));
		$this->assertSame($variationUser1, $test->getVariationFixture($user1));

		$test->delete();
	}

	public function testGetVariationFixtureCount() {
		$user1 = TH::createUser();
		$user2 = TH::createUser();
		/** @var CM_Model_Splittest $test */
		$test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));

		$this->assertSame(0, $test->getVariationFixtureCount());
		$test->getVariationFixture($user1);
		$this->assertSame(1, $test->getVariationFixtureCount());
		$test->getVariationFixture($user1);
		$this->assertSame(1, $test->getVariationFixtureCount());
		$test->getVariationFixture($user2);
		$this->assertSame(2, $test->getVariationFixtureCount());

		$test->delete();
	}

	public function testGetConversionCount() {
		$user1 = TH::createUser();
		/** @var CM_Model_Splittest $test */
		$test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
		$variations = $test->getVariations();

		$variationFixtureName = $test->getVariationFixture($user1);
		$variationFixtureId = array_search($variationFixtureName, $variations);
		$this->assertSame(0, $test->getConversionCount($variationFixtureId));

		$test->setConversion($user1);
		$this->assertSame(1, $test->getConversionCount($variationFixtureId));

		$test->delete();
	}

	public function testGetSetRunning() {
		/** @var CM_Model_Splittest $test */
		$test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
		$this->assertTrue($test->getRunning());

		$test->setRunning(true);
		$this->assertTrue($test->getRunning());
		$test->setRunning(false);
		$this->assertFalse($test->getRunning());

		$test->delete();
	}

	public function testDelete() {
		$test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
		$test->delete();
		try {
			new CM_Model_Splittest($test->getId());
			$this->fail('Splittest not deleted.');
		} catch (CM_Exception_Nonexistent $e) {
			$this->assertTrue(true);
		}
	}

}
