<?php

class CM_Paging_Currency_AllTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testPaging() {
        $currency1 = CM_Model_Currency::create('123', 'FOO');
        $currency2 = CM_Model_Currency::create('456', 'BAR');

        $paging = new CM_Paging_Currency_All();
        $this->assertContainsAll([$currency1, $currency2], $paging);
    }
}
