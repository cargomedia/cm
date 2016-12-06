<?php

class CM_Site_SiteSettingsTest extends CMTest_TestCase {

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        $siteSettings = CM_Site_SiteSettings::create(4, 'Baz', CM_Params::factory(['foo' => 'bar', 'baz' => 4]));
        $this->assertInstanceOf('CM_Site_SiteSettings', $siteSettings);

        $this->assertSame(4, $siteSettings->getSiteId());
        $this->assertEquals(CM_Params::factory(['foo' => 'bar', 'baz' => 4]), $siteSettings->getConfiguration());
        $this->assertSame('Baz', $siteSettings->getName());

        $siteSettings->setSiteId(1);
        $siteSettings->setName('Quux');
        $siteSettings->setConfiguration(CM_Params::factory(['bar' => 'foo']));

        $siteSettings->_change();

        $this->assertSame(1, $siteSettings->getSiteId());
        $this->assertEquals(CM_Params::factory(['bar' => 'foo']), $siteSettings->getConfiguration());
        $this->assertSame('Quux', $siteSettings->getName());
    }

    public function testFindById() {
        $this->assertNull(CM_Site_SiteSettings::findBySiteId(5));
        $siteSettings = CM_Site_SiteSettings::create(5, 'quux', CM_Params::factory(['foo' => 'baz', 'baz' => 5]));
        $foundSiteSettings = CM_Site_SiteSettings::findBySiteId(5);
        $this->assertInstanceOf('CM_Site_SiteSettings', $foundSiteSettings);
        $this->assertEquals($siteSettings, $foundSiteSettings);
    }
}
