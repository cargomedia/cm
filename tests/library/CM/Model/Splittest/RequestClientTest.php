<?php

class CM_Model_Splittest_RequestClientTest extends CMTest_TestCase {

	public function setUp() {
		CM_Config::get()->CM_Model_Splittest->withoutPersistence = false;
	}

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
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

	public  function testSetConversion() {
		$request = new CM_Request_Post('/foo/' . CM_Site_CM::TYPE);
		$request2 = new CM_Request_Post('/foo/' . CM_Site_CM::TYPE);

		/** @var CM_Model_Splittest_RequestClient $test */
		$test = CM_Model_Splittest_RequestClient::create(array('name' => 'bar', 'variations' => array('v1')));
		/** @var CM_Model_SplittestVariation $variation */
		$variation = $test->getVariations()->getItem(0);

		$test->isVariationFixture($request, 'v1');
		$test->isVariationFixture($request2, 'v1');
		$this->assertSame(0, $variation->getConversionCount());
		$test->setConversion($request);
		$this->assertSame(1, $variation->getConversionCount());
		$test->setConversion($request2, 2.5);
		$this->assertSame(1.75, $variation->getConversionRate());

		$test->delete();
	}
}
