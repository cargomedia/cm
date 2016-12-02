<?php

class CM_Site_SiteSettingsTest extends CMTest_TestCase {

    public function testCreate() {
        $siteSettings = CM_Site_SiteSettings::create(4, CM_Params::factory(['foo' => 'bar', 'baz' => 4]), 'Baz');
        $this->assertInstanceOf('CM_Site_SiteSettings', $siteSettings);

        $this->assertSame(4, $siteSettings->getClassType());
        $this->assertEquals(CM_Params::factory(['foo' => 'bar', 'baz' => 4]), $siteSettings->getSettings());
        $this->assertSame('Baz', $siteSettings->getName());

        $siteSettings->setClassType(1);
        $siteSettings->setName('Quux');
        $siteSettings->setSettings(CM_Params::factory(['bar' => 'foo']));

        $siteSettings->_change();

        $this->assertSame(1, $siteSettings->getClassType());
        $this->assertEquals(CM_Params::factory(['bar' => 'foo']), $siteSettings->getSettings());
        $this->assertSame('Quux', $siteSettings->getName());
    }
}
