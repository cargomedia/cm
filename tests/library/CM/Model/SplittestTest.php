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
		$this->assertInstanceOf('CM_Paging_SplittestVariation_Splittest', $test->getVariations());

		$test->delete();
	}

	public function testGetVariationFixture() {
		$user1 = TH::createUser();
		/** @var CM_Model_Splittest $test */
		$test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));

		for ($i = 0; $i < 2; $i++) {
			$variationUser1 = $test->getVariationFixture($user1);
			$this->assertContains($variationUser1, array('v1', 'v2'));
			$this->assertSame($variationUser1, $test->getVariationFixture($user1));
		}

		$test->delete();
	}

	public function testGetVariationFixtureDisabledVariation() {
		/** @var CM_Model_Splittest $test */
		$test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
		/** @var CM_Model_SplittestVariation $variation1 */
		$variation1 = $test->getVariations()->getItem(0);
		/** @var CM_Model_SplittestVariation $variation2 */
		$variation2 = $test->getVariations()->getItem(1);

		$variation1->setEnabled(false);
		for ($i = 0; $i < 10; $i++) {
			$user = TH::createUser();
			$this->assertSame($variation2->getName(), $test->getVariationFixture($user));
		}

		$test->delete();
	}

	public function testGetVariationFixtureWithVariationName() {
		$user1 = TH::createUser();

		for ($i = 0; $i < 5; $i++) {
			/** @var CM_Model_Splittest $test */
			$test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
			$this->assertSame('v1', $test->getVariationFixture($user1, 'v1'));
			$this->assertSame('v1', $test->getVariationFixture($user1));

			$test->delete();
		}
	}

	public function testGetVariationFixtureWithVariationNameInvalid() {
		$user1 = TH::createUser();

		/** @var CM_Model_Splittest $test */
		$test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
		try {
			$test->getVariationFixture($user1, 'v3');
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
		$user1 = TH::createUser();
		/** @var CM_Model_Splittest $test1 */
		$test1 = CM_Model_Splittest::create(array('name' => 'foo1', 'variations' => array('v1', 'v2')));
		/** @var CM_Model_Splittest $test2 */
		$test2 = CM_Model_Splittest::create(array('name' => 'foo2', 'variations' => array('w1', 'w2')));

		$this->assertContains($test1->getVariationFixture($user1), array('v1', 'v2'));
		$this->assertContains($test2->getVariationFixture($user1), array('w1', 'w2'));

		$test1->delete();
		$test2->delete();
	}

}
