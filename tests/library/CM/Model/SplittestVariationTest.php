<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_Model_SplittestVariationTest extends TestCase {
	/** @var CM_Model_Splittest */
	private $_test;

	public function setUp() {
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
			$this->assertModelEquals($this->_test, $variation->getSplittest());
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
		$fixtureId = rand(1, 99999999);

		/** @var CM_Model_Splittest_VariationMock $test */
		$test = CM_Model_Splittest_VariationMock::create(array('name' => 'bar', 'variations' => array('v1')));
		/** @var CM_Model_SplittestVariation $variation */
		$variation = $test->getVariations()->getItem(0);

		$test->getVariationFixture($fixtureId);
		$this->assertSame(0, $variation->getConversionCount());
		$test->setConversion($fixtureId);
		$this->assertSame(1, $variation->getConversionCount());

		$test->delete();
	}

	public function testGetFixtureCount() {
		$fixtureId = rand(1, 99999999);
		$fixtureId2 = rand(1, 99999999);

		/** @var CM_Model_Splittest_VariationMock $test */
		$test = CM_Model_Splittest_VariationMock::create(array('name' => 'bar', 'variations' => array('v1')));

		/** @var CM_Model_SplittestVariation $variation */
		$variation = $test->getVariations()->getItem(0);

		$this->assertSame(0, $variation->getFixtureCount());

		$test->getVariationFixture($fixtureId);
		$this->assertSame(1, $variation->getFixtureCount());
		$test->getVariationFixture($fixtureId);
		$this->assertSame(1, $variation->getFixtureCount());
		$test->getVariationFixture($fixtureId2);
		$this->assertSame(2, $variation->getFixtureCount());

		$test->delete();
	}

}

class CM_Model_Splittest_VariationMock extends CM_Model_Splittest {

	const TYPE = 1;

	/**
	 * @param  int            $fixtureId
	 * @param  string|null    $variationName
	 * @return string
	 */
	public function getVariationFixture($fixtureId, $variationName = null) {
		return $this->_getVariationFixture($fixtureId, $variationName);
	}

	/**
	 * @param int $fixtureId
	 */
	public function setConversion($fixtureId) {
		$this->_setConversion($fixtureId);
	}
}