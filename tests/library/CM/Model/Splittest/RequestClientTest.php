<?php

class CM_Model_Splittest_RequestClientTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testIsVariationFixture() {
        $request = new CM_Request_Post('/foo/null');
        /** @var CM_Model_Splittest_RequestClient $test */
        $test = CM_Model_Splittest_RequestClient::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2')));

        for ($i = 0; $i < 2; $i++) {
            $variationUser1 = $test->isVariationFixture($request, 'v1');
            $this->assertSame($variationUser1, $test->isVariationFixture($request, 'v1'));
        }
    }

    public function testSetConversion() {
        $request = new CM_Request_Post('/foo/null');
        $request2 = new CM_Request_Post('/foo/null');

        /** @var CM_Model_Splittest_RequestClient $test */
        $test = CM_Model_Splittest_RequestClient::createStatic(array('name' => 'bar', 'variations' => array('v1')));
        /** @var CM_Model_SplittestVariation $variation */
        $variation = $test->getVariations()->getItem(0);

        $test->isVariationFixture($request, 'v1');
        $test->isVariationFixture($request2, 'v1');
        $this->assertSame(0, $variation->getConversionCount(true));
        $test->setConversion($request);
        $this->assertSame(1, $variation->getConversionCount(true));
        $test->setConversion($request2, 2.5);
        $this->assertSame(1.75, $variation->getConversionRate(true));
    }

    public function testIgnoreBots() {
        $request = new CM_Request_Get('/foo', array('user-agent' => 'Googlebot'));
        /** @var CM_Model_Splittest_RequestClient $test */
        $test = CM_Model_Splittest_RequestClient::createStatic(array('name' => 'foo', 'variations' => array('v1')));
        $this->assertFalse($test->isVariationFixture($request, 'v1'));
    }

    public function testIsVariationFixtureStatic() {
        $request = $this->createRequest('/');
        $this->assertFalse(CM_Model_Splittest_RequestClient::isVariationFixtureStatic('foo', $request, 'bar'));
        CM_Model_Splittest_RequestClient::create('foo', ['bar']);
        $this->assertTrue(CM_Model_Splittest_RequestClient::isVariationFixtureStatic('foo', $request, 'bar'));
    }

    public function testSetConversionStatic() {
        $request1 = $this->createRequest('/');
        $request2 = $this->createRequest('/');

        CM_Model_Splittest_RequestClient::setConversionStatic('foo', $request1);
        $splittest = CM_Model_Splittest_RequestClient::create('foo', ['bar']);

        /** @var CM_Model_SplittestVariation $variation */
        $variation = $splittest->getVariations()->getItem(0);
        $splittest->isVariationFixture($request1, 'bar');
        $splittest->isVariationFixture($request2, 'bar');

        $this->assertSame(0, $variation->getConversionCount(true));
        CM_Model_Splittest_RequestClient::setConversionStatic('foo', $request1);
        $this->assertSame(1, $variation->getConversionCount(true));
        CM_Model_Splittest_RequestClient::setConversionStatic('foo', $request2, 2.5);
        $this->assertSame(1.75, $variation->getConversionRate(true));
    }
}
