<?php

class CM_Model_SplittestTest extends CMTest_TestCase {

	public function setUp() {
		CM_Config::get()->CM_Model_Splittest->withoutPersistence = false;
	}

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
		$this->assertEquals($test, $test2);

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
		$this->assertInstanceOf('CM_Paging_SplittestVariation_Splittest', $test->getVariations());

		$test->delete();
	}

	public function testIsVariationFixtureDisabledVariation() {
		/** @var CM_Model_Splittest_Mock $test */
		$test = CM_Model_Splittest_Mock::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
		/** @var CM_Model_SplittestVariation $variation1 */
		$variation1 = $test->getVariations()->getItem(0);
		/** @var CM_Model_SplittestVariation $variation2 */
		$variation2 = $test->getVariations()->getItem(1);

		$variation1->setEnabled(false);
		for ($i = 0; $i < 10; $i++) {
			$user = CMTest_TH::createUser();
			$this->assertTrue($test->isVariationFixture(new CM_Splittest_Fixture($user), $variation2->getName()));
		}

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

	public function testGetVariationFixtureMultiple() {
		$user = CMTest_TH::createUser();
		$fixture = new CM_Splittest_Fixture($user);

		/** @var CM_Model_Splittest_Mock $test1 */
		$test1 = CM_Model_Splittest_Mock::create(array('name' => 'foo1', 'variations' => array('v1', 'v2')));
		/** @var CM_Model_Splittest_Mock $test2 */
		$test2 = CM_Model_Splittest_Mock::create(array('name' => 'foo2', 'variations' => array('w1', 'w2')));

		$this->assertContains($test1->getVariationFixture($fixture), array('v1', 'v2'));
		$this->assertContains($test2->getVariationFixture($fixture), array('w1', 'w2'));

		$test1->delete();
		$test2->delete();
	}

	public function testIsVariationFixture() {
		$user = CMTest_TH::createUser();
		$fixture = new CM_Splittest_Fixture($user);

		/** @var CM_Model_Splittest_Mock $test */
		$test = CM_Model_Splittest_Mock::create(array('name' => 'foo1', 'variations' => array('v1', 'v2')));
		$this->assertTrue($test->isVariationFixture($fixture, $test->getVariationFixture($fixture)));
		$this->assertFalse($test->isVariationFixture($fixture, 'noVariation'));
	}

	public function testWithoutPersistence() {
		$user = CMTest_TH::createUser();
		$fixture = new CM_Splittest_Fixture($user);

		CM_Config::get()->CM_Model_Splittest->withoutPersistence = true;
		$test = new CM_Model_Splittest_Mock('notExisting');

		$this->assertTrue($test->isVariationFixture($fixture, 'bar'));
		$this->assertSame('', $test->getVariationFixture($fixture));
		$test->setConversion($fixture);

		CMTest_TH::clearConfig();
	}
}

class CM_Model_Splittest_Mock extends CM_Model_Splittest {

	const TYPE = 1;

	/**
	 * @param CM_Splittest_Fixture $fixture
	 * @param string               $variationName
	 * @return bool
	 */
	public function isVariationFixture(CM_Splittest_Fixture $fixture, $variationName) {
		return $this->_isVariationFixture($fixture, $variationName);
	}

	/**
	 * @param  CM_Splittest_Fixture $fixture
	 * @return string
	 */
	public function getVariationFixture(CM_Splittest_Fixture $fixture) {
		return $this->_getVariationFixture($fixture);
	}

	/**
	 * @param CM_Splittest_Fixture $fixtureId
	 */
	public function setConversion(CM_Splittest_Fixture $fixture) {
		$this->_setConversion($fixture);
	}
}
