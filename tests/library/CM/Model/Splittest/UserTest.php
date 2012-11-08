<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Model_Splittest_UserTest extends TestCase {

	public function testGetVariationFixture() {
		$user = TH::createUser();

		/** @var CM_Model_Splittest_User $test */
		$test = CM_Model_Splittest_User::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));

		for ($i = 0; $i < 2; $i++) {
			$variationUser1 = $test->getVariationFixture($user);
			$this->assertContains($variationUser1, array('v1', 'v2'));
			$this->assertSame($variationUser1, $test->getVariationFixture($user));
		}

		$test->delete();
	}

	public  function testSetConversion() {
		$user = TH::createUser();

		/** @var CM_Model_Splittest_User $test */
		$test = CM_Model_Splittest_User::create(array('name' => 'bar', 'variations' => array('v1')));
		/** @var CM_Model_SplittestVariation $variation */
		$variation = $test->getVariations()->getItem(0);

		$test->getVariationFixture($user);
		$this->assertSame(0, $variation->getConversionCount());
		$test->setConversion($user);
		$this->assertSame(1, $variation->getConversionCount());

		$test->delete();
	}
}
