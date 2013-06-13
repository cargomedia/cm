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

	public function testGetSignificance() {
		foreach (array(
					 array(0, 0, 0, 0, 0, 0, null),
					 array(1, 0, 0, 0, 0, 0, null),
					 array(1, 1, 1, 0, 0, 0, null),
					 array(1, 1, 1, 1, 0, 0, null),
					 array(1, 1, 1, 1, 1, 1, null),

					 array(1000, 0, 0, 1000, 0, 0, null),
					 array(1000, 1, 1, 1000, 0, 0, null),
					 array(1000, 1, 1, 1000, 1, 1, null),
					 array(1000, 1, 1, 1000, 2, 2, null),
					 array(1000, 9, 9, 1000, 8, 8, null),
					 array(1000, 9, 9, 1000, 9, 9, null),
					 array(1000, 9, 9, 1000, 10, 10, 0.9832350325711),
					 array(1000, 10, 10, 1000, 10, 10, 1.0),
					 array(1000, 10, 10, 1000, 11, 11, 0.98480372092235),

					 array(1000, 250, 250, 1000, 250, 250, 1.0),
					 array(1000, 240, 240, 1000, 260, 260, 0.71399839213865),
					 array(1000, 230, 230, 1000, 270, 270, 0.26748146746717),
					 array(1000, 220, 220, 1000, 280, 280, 0.056109517207276),
					 array(1000, 210, 210, 1000, 290, 290, 0.0069618505811649),

					 array(1000, 250, 25000, 1000, 250, 25000, 1.0),
					 array(1000, 240, 24000, 1000, 260, 26000, 0.71399839213865),
					 array(1000, 230, 23000, 1000, 270, 27000, 0.26748146746717),
					 array(1000, 220, 22000, 1000, 280, 28000, 0.056109517207276),
					 array(1000, 210, 21000, 1000, 290, 29000, 0.0069618505811649),

					 array(1000, 500, 250, 1000, 250, 250, 1.0),
					 array(1000, 480, 240, 1000, 260, 260, 0.57532403980404),
					 array(1000, 460, 230, 1000, 270, 270, 0.15389428823649),
					 array(1000, 440, 220, 1000, 280, 280, 0.028603332811777),
					 array(1000, 420, 210, 1000, 290, 290, 0.0034874234668595),

					 array(1000, 500, 25000, 1000, 250, 25000, 1.0),
					 array(1000, 480, 24000, 1000, 260, 26000, 0.57532403980404),
					 array(1000, 460, 23000, 1000, 270, 27000, 0.15389428823649),
					 array(1000, 440, 22000, 1000, 280, 28000, 0.028603332811777),
					 array(1000, 420, 21000, 1000, 290, 29000, 0.0034874234668595),
				 ) as $list) {
			list($fixturesA, $conversionsA, $weightA, $fixturesB, $conversionsB, $weightB, $significance) = $list;
			$variationA = $this->_getVariationMock($fixturesA, $conversionsA, $weightA);
			$variationB = $this->_getVariationMock($fixturesB, $conversionsB, $weightB);
			$this->assertSame($significance, $variationA->getSignificance($variationB));
			$this->assertSame($significance, $variationB->getSignificance($variationA));
		}
	}

	/**
	 * @param int   $fixture
	 * @param int   $conversion
	 * @param float $weight
	 * @return CM_Model_SplittestVariation
	 */
	protected function _getVariationMock($fixture, $conversion, $weight) {
		$variation = $this->getMockBuilder('CM_Model_SplittestVariation')->disableOriginalConstructor()
				->setMethods(array('getFixtureCount', 'getConversionCount', 'getConversionWeight'))->getMock();
		$variation->expects($this->any())->method('getFixtureCount')->will($this->returnValue($fixture));
		$variation->expects($this->any())->method('getConversionCount')->will($this->returnValue($conversion));
		$variation->expects($this->any())->method('getConversionWeight')->will($this->returnValue($weight));
		return $variation;
	}
}
