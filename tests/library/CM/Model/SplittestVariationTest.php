<?php

class CM_Model_SplittestVariationTest extends CMTest_TestCase {
	/** @var CM_Model_Splittest */
	private $_test;

	public function setUp() {
		CM_Config::get()->CM_Model_Splittest->withoutPersistence = false;
		$this->_test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
	}

	public function tearDown() {
		$this->_test->delete();
	}

	public function testConstruct() {
		/** @var CM_Model_SplittestVariation $variation */
		foreach ($this->_test->getVariations() as $variation) {
			new CM_Model_SplittestVariation($variation->getId());
			$this->assertTrue(true);
		}
	}

	public function testGetId() {
		/** @var CM_Model_SplittestVariation $variation */
		foreach ($this->_test->getVariations() as $variation) {
			$this->assertInternalType('int', $variation->getId());
			$this->assertGreaterThanOrEqual(1, $variation->getId());
		}
	}

	public function testGetName() {
		/** @var CM_Model_SplittestVariation $variation */
		foreach ($this->_test->getVariations() as $variation) {
			$this->assertInternalType('string', $variation->getName());
			$this->assertContains($variation->getName(), array('v1', 'v2'));
		}
	}

	public function testGetSplittest() {
		/** @var CM_Model_SplittestVariation $variation */
		foreach ($this->_test->getVariations() as $variation) {
			$this->assertEquals($this->_test, $variation->getSplittest());
		}
	}

	public function testGetSetEnabled() {
		/** @var CM_Model_SplittestVariation $variation1 */
		$variation1 = $this->_test->getVariations()->getItem(0);
		/** @var CM_Model_SplittestVariation $variation2 */
		$variation2 = $this->_test->getVariations()->getItem(0);

		$this->assertTrue($variation1->getEnabled());
		$this->assertTrue($variation2->getEnabled());

		$variation1->setEnabled(false);
		$this->assertFalse($variation1->getEnabled());
		$variation1->setEnabled(true);
		$this->assertTrue($variation1->getEnabled());

		$variation1->setEnabled(false);
		try {
			$variation2->setEnabled(false);
			$this->fail('Could disable all variations');
		} catch (CM_Exception $e) {
			$this->assertTrue(true);
		}
	}

	public function testGetConversionCount() {
		$user = CMTest_TH::createUser();

		/** @var CM_Model_Splittest_User $test */
		$test = CM_Model_Splittest_User::create(array('name' => 'bar', 'variations' => array('v1')));
		/** @var CM_Model_SplittestVariation $variation */
		$variation = $test->getVariations()->getItem(0);

		$test->isVariationFixture($user, 'v1');
		$this->assertSame(0, $variation->getConversionCount());
		$test->setConversion($user);
		$this->assertSame(1, $variation->getConversionCount());

		$test->delete();
	}

	public function testGetConversionWeight() {
		$user = CMTest_TH::createUser();
		$user2 = CMTest_TH::createUser();
		$user3 = CMTest_TH::createUser();

		/** @var CM_Model_Splittest_User $test */
		$test = CM_Model_Splittest_User::create(array('name' => 'bar', 'variations' => array('v1')));
		/** @var CM_Model_SplittestVariation $variation */
		$variation = $test->getVariations()->getItem(0);

		$test->isVariationFixture($user, 'v1');
		$test->isVariationFixture($user2, 'v1');
		$test->isVariationFixture($user3, 'v1');
		$this->assertSame(0.0, $variation->getConversionWeight());
		$test->setConversion($user, 3.75);
		$test->setConversion($user2, 3.29);
		$this->assertSame(7.04, $variation->getConversionWeight());
		$this->assertSame(2.3466666666667, $variation->getConversionRate());

		try {
			$test->setConversion($user, -2);
			$this->fail('Could set Conversion with negative weight');
		} catch (CM_Exception_Invalid $e) {
			$this->assertTrue(true);
		}

		$test->delete();
	}

	public function testGetFixtureCount() {
		$user1 = CMTest_TH::createUser();
		$user2 = CMTest_TH::createUser();

		/** @var CM_Model_Splittest_User $test */
		$test = CM_Model_Splittest_User::create(array('name' => 'bar', 'variations' => array('v1')));

		/** @var CM_Model_SplittestVariation $variation */
		$variation = $test->getVariations()->getItem(0);

		$this->assertSame(0, $variation->getFixtureCount());

		$test->isVariationFixture($user1, 'v1');
		$this->assertSame(1, $variation->getFixtureCount());
		$test->isVariationFixture($user1, 'v1');
		$this->assertSame(1, $variation->getFixtureCount());
		$test->isVariationFixture($user2, 'v1');
		$this->assertSame(2, $variation->getFixtureCount());

		$test->delete();
	}
}
