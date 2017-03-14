<?php

class CM_Paging_Site_AllTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testPaging() {
        $this->assertSame(1, (new CM_Paging_Site_All())->getCount()); //mockSiteDefault
        $this->getMockSite();
        $this->getMockSite();

        $this->assertSame(3, (new CM_Paging_Site_All())->getCount());
        $this->assertInstanceOf('CM_Site_Abstract', (new CM_Paging_Site_All())->getItem(0));
    }
}
