<?php

class CM_Paging_Site_AllTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testPaging() {
        $defaultSite = (new CM_Site_SiteFactory())->getDefaultSite();
        $this->assertEquals([$defaultSite], (new CM_Paging_Site_All())->getItems());
        $site2 = $this->getMockSite();
        $site3 = $this->getMockSite();

        $this->assertEquals([$defaultSite, $site2, $site3], (new CM_Paging_Site_All())->getItems());
    }
}
