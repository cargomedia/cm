<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Model_Splittest_RequestClientTest extends TestCase {

	public function setUp() {
		CM_Config::get()->CM_Model_Splittest->withoutPersistence = false;
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testIsVariationFixture() {
		$request = new CM_Request_Post('/foo/' . CM_Site_CM::TYPE);
		/** @var CM_Model_Splittest_RequestClient $test */
		$test = CM_Model_Splittest_RequestClient::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));

		for ($i = 0; $i < 2; $i++) {
			$variationUser1 = $test->isVariationFixture($request, 'v1');
			$this->assertSame($variationUser1, $test->isVariationFixture($request, 'v1'));
		}

		$test->delete();
	}

	public function testGetVariationFixture() {
		$request = new CM_Request_Post('/foo/' . CM_Site_CM::TYPE);
		/** @var CM_Model_Splittest_RequestClient $test */
		$test = CM_Model_Splittest_RequestClient::create(array('name' => 'foo', 'variations' => array('v1', 'v2')));

		for ($i = 0; $i < 2; $i++) {
			$variationUser1 = $test->getVariationFixture($request, 'v1');
			$this->assertSame($variationUser1, $test->getVariationFixture($request, 'v1'));
		}

		$test->delete();
	}

	public  function testSetConversion() {
		$request = new CM_Request_Post('/foo/' . CM_Site_CM::TYPE);
		/** @var CM_Model_Splittest_RequestClient $test */
		$test = CM_Model_Splittest_RequestClient::create(array('name' => 'bar', 'variations' => array('v1')));
		/** @var CM_Model_SplittestVariation $variation */
		$variation = $test->getVariations()->getItem(0);

		$test->isVariationFixture($request, 'v1');
		$this->assertSame(0, $variation->getConversionCount());
		$test->setConversion($request);
		$this->assertSame(1, $variation->getConversionCount());

		$test->delete();
	}
}
