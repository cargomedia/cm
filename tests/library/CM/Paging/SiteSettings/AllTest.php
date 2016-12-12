<?php
class CM_Paging_SiteSettings_AllTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testAll() {
        $paging = new CM_Paging_SiteSettings_All();
        $this->assertSame(0, $paging->getCount());

        $settingsAdded = CM_Site_SiteSettings::create(1, 'Baz', CM_Params::factory(['bar' => 'foo']));
        $paging = new CM_Paging_SiteSettings_All();
        $this->assertSame(1, $paging->getCount());
        $settings = $paging->getItem(0);
        $this->assertInstanceOf('CM_Site_SiteSettings', $settings);
        $this->assertEquals($settingsAdded, $settings);
    }
}
