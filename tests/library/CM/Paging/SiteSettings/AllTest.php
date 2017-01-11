<?php
class CM_Paging_SiteSettings_AllTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testAll() {
        $paging = new CM_Paging_SiteSettings_All();
        $this->assertSame(1, $paging->getCount()); //site created in CMTest_TestCase::runBare() counts

        $settingsAdded = CM_Site_SiteSettings::create(1, 'Baz', CM_Params::factory(['bar' => 'foo']));
        $paging = new CM_Paging_SiteSettings_All();
        $this->assertSame(2, $paging->getCount());
        $settings = $paging->getItem(1);
        $this->assertInstanceOf('CM_Site_SiteSettings', $settings);
        $this->assertEquals($settingsAdded, $settings);
    }
}
