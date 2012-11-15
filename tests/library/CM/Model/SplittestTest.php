<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_Model_SplittestTest extends TestCase {

	public function setUp() {
		CM_Config::get()->CM_Model_Splittest->withoutPersistence = false;
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
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
		$this->assertInstanceOf('CM_Paging_SplittestVariation_Splittest', $test->getVariations());

		$test->delete();
	}

	public function testGetVariationFixture() {
		$fixtureId = rand(1, 99999999);
		/** @var CM_Model_Splittest $test */
		$test = CM_Model_Splittest_Mock::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));

		for ($i = 0; $i < 2; $i++) {
			$variationUser1 = $test->getVariationFixture($fixtureId);
			$this->assertContains($variationUser1, array('v1', 'v2'));
			$this->assertSame($variationUser1, $test->getVariationFixture($fixtureId));
		}

		$test->delete();
	}

	public function testGetVariationFixtureDisabledVariation() {
		/** @var CM_Model_Splittest_Mock $test */
		$test = CM_Model_Splittest_Mock::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
		/** @var CM_Model_SplittestVariation $variation1 */
		$variation1 = $test->getVariations()->getItem(0);
		/** @var CM_Model_SplittestVariation $variation2 */
		$variation2 = $test->getVariations()->getItem(1);

		$variation1->setEnabled(false);
		for ($i = 0; $i < 10; $i++) {
			$fixtureId = rand(1, 99999999);
			$this->assertSame($variation2->getName(), $test->getVariationFixture($fixtureId));
		}

		$test->delete();
	}

	public function testGetVariationFixtureWithVariationName() {
		$fixtureId = rand(1, 99999999);

		for ($i = 0; $i < 5; $i++) {
			/** @var CM_Model_Splittest_Mock $test */
			$test = CM_Model_Splittest_Mock::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
			$this->assertSame('v1', $test->getVariationFixture($fixtureId, 'v1'));
			$this->assertSame('v1', $test->getVariationFixture($fixtureId));

			$test->delete();
		}
	}

	public function testGetVariationFixtureWithVariationNameEmpty() {
		$fixtureId = rand(1, 99999999);

		/** @var CM_Model_Splittest_Mock $test */
		$test = CM_Model_Splittest_Mock::create(array('name' => 'foo', 'variations' => array('0', '1', '2', '3')));
		$this->assertSame('0', $test->getVariationFixture($fixtureId, '0'));
		$this->assertSame('0', $test->getVariationFixture($fixtureId));

		$test->delete();
	}

	public function testGetVariationFixtureWithVariationNameInvalid() {
		$fixtureId = rand(1, 99999999);

		/** @var CM_Model_Splittest_Mock $test */
		$test = CM_Model_Splittest_Mock::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
		try {
			$test->getVariationFixture($fixtureId, 'v3');
			$this->fail('Could get variation fixture with invalid variationName');
		} catch (CM_Exception_Invalid $e) {
			$this->assertContains('has no variation `v3`', $e->getMessage());
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
		$fixtureId = rand(1, 99999999);

		/** @var CM_Model_Splittest_Mock $test1 */
		$test1 = CM_Model_Splittest_Mock::create(array('name' => 'foo1', 'variations' => array('v1', 'v2')));
		/** @var CM_Model_Splittest_Mock $test2 */
		$test2 = CM_Model_Splittest_Mock::create(array('name' => 'foo2', 'variations' => array('w1', 'w2')));

		$this->assertContains($test1->getVariationFixture($fixtureId), array('v1', 'v2'));
		$this->assertContains($test2->getVariationFixture($fixtureId), array('w1', 'w2'));

		$test1->delete();
		$test2->delete();
	}

	public function testIsVariationFixture() {
		$fixtureId = rand(1, 999999999);
		$fixtureId2 = $fixtureId + 1;
		$fixtureId3 = $fixtureId2 + 1;

		/** @var CM_Model_Splittest_Mock $test */
		$test = CM_Model_Splittest_Mock::create(array('name' => 'foo1', 'variations' => array('v1', 'v2')));
		$isVariationFixture = $test->isVariationFixture($fixtureId, 'v1');
		$this->assertSame($isVariationFixture, $test->isVariationFixture($fixtureId, 'v1'));

		$this->assertTrue($test->isVariationFixture($fixtureId2, 'v1', 'v1'));
		$this->assertFalse($test->isVariationFixture($fixtureId3, 'v1', 'v2'));

	}

	public function testWithoutPersistence() {
		$fixtureId = rand(1, 999999999);

		CM_Config::get()->CM_Model_Splittest->withoutPersistence = true;
		$test = new CM_Model_Splittest_Mock('notExisting');

		$this->assertTrue($test->isVariationFixture($fixtureId, 'bar'));
		$this->assertSame('', $test->getVariationFixture($fixtureId));
		$test->setConversion($fixtureId);

		TH::clearConfig();
	}

}

class CM_Model_Splittest_Mock extends CM_Model_Splittest {

	const TYPE = 1;

	/**
	 * @param int         $fixtureId
	 * @param string      $variationName
	 * @param string|null $forceVariationName
	 * @return bool
	 */
	public function isVariationFixture($fixtureId, $variationName, $forceVariationName = null) {
		return $this->_isVariationFixture($fixtureId, $variationName, $forceVariationName);
	}

	/**
	 * @param  int            $fixtureId
	 * @param  string|null    $forceVariationName
	 * @return string
	 */
	public function getVariationFixture($fixtureId, $forceVariationName = null) {
		return $this->_getVariationFixture($fixtureId, $forceVariationName);
	}

	/**
	 * @param int $fixtureId
	 */
	public function setConversion($fixtureId) {
		$this->_setConversion($fixtureId);
	}
}
