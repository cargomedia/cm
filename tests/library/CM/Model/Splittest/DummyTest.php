<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Model_Splittest_DummyTest extends TestCase {

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testGetVariationFixture() {
		CM_Config::get()->CM_Model_Splittest->forceAllVariations = true;
		$user = TH::createUser();

		/** @var CM_Model_Splittest $test */
		$test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));
		$testInstance = CM_Model_Splittest::getInstance('foo');

		for ($i = 0; $i < 2; $i++) {
			$this->assertTrue($testInstance->isVariationFixture($user, 'v1'));
		}

		$test->delete();
	}

	public function testGetDummyInstance() {
		$test = CM_Model_Splittest::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));

		CM_Config::get()->CM_Model_Splittest->forceAllVariations = false;
		$this->assertInstanceOf('CM_Model_Splittest', CM_Model_Splittest::getInstance('foo'));

		CM_Config::get()->CM_Model_Splittest->forceAllVariations = true;
		$this->assertInstanceOf('CM_Model_Splittest_Dummy', CM_Model_Splittest::getInstance('foo'));

		$test->delete();
	}

}
