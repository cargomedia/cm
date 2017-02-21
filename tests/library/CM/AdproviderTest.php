<?php

class CM_AdproviderTest extends CMTest_TestCase {

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testGetHtml() {
        $site = $this->getMockSite();

        CM_Config::get()->CM_Adprovider->enabled = true;
        CM_Config::get()->CM_Adprovider->zones = array('foo' => array('adapter' => 'CM_AdproviderAdapter_Mock', 'zoneId' => 1));
        $adprovider = new CM_Adprovider();

        $this->assertSame('{"zoneData":{"zoneId":1},"variables":[]}', $adprovider->getHtml($site, 'foo'));
        $this->assertSame('{"zoneData":{"zoneId":1},"variables":{"foo":"bar"}}', $adprovider->getHtml($site, 'foo', array('foo' => 'bar')));

        CM_Config::get()->CM_Adprovider->enabled = false;
        $this->assertSame('', $adprovider->getHtml($site, 'foo'));
    }

    public function testGetHtmlWithSiteConfig() {
        $site = $this->getMockSite();
        $siteClassName = get_class($site);

        CM_Config::get()->CM_Adprovider->enabled = true;
        CM_Config::get()->CM_Adprovider->zones = array('foo' => array('adapter' => 'CM_AdproviderAdapter_Mock', 'zoneId' => 1));
        CM_Config::get()->$siteClassName->CM_Adprovider = new stdClass();
        CM_Config::get()->$siteClassName->CM_Adprovider->zones = array('foo' => array('adapter' => 'CM_AdproviderAdapter_Mock', 'zoneId' => 2));
        $adprovider = new CM_Adprovider();

        $this->assertSame('{"zoneData":{"zoneId":2},"variables":[]}', $adprovider->getHtml($site, 'foo'));
        $this->assertSame('{"zoneData":{"zoneId":2},"variables":{"foo":"bar"}}', $adprovider->getHtml($site, 'foo', array('foo' => 'bar')));

        CM_Config::get()->CM_Adprovider->enabled = false;
        $this->assertSame('', $adprovider->getHtml($site, 'foo'));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Invalid ad adapter
     */
    public function testGetHtmlInvalidAdapter() {
        $site = $this->getMockSite();

        CM_Config::get()->CM_Adprovider->enabled = true;
        CM_Config::get()->CM_Adprovider->zones = array('foo' => array('adapter' => 'CM_AdproviderAdapter_Nonexistent'));
        $adprovider = new CM_Adprovider();

        $adprovider->getHtml($site, 'foo');
    }

    public function testGetHtmlMissingConfig() {
        $site = $this->getMockSite();
        CM_Config::get()->CM_Adprovider->enabled = true;
        $adprovider = new CM_Adprovider();
        $exception = $this->catchException(function () use ($adprovider,$site ) {
            $adprovider->getHtml($site, 'foo');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Zone not configured.', $exception->getMessage());
        $this->assertSame(['zoneName' => 'foo'], $exception->getMetaInfo());
    }

    public function testHasZone() {
        $site = $this->getMockSite();

        CM_Config::get()->CM_Adprovider->enabled = true;
        CM_Config::get()->CM_Adprovider->zones = array('foo' => array('adapter' => 'CM_AdproviderAdapter_Nonexistent'));
        $adprovider = new CM_Adprovider();

        $this->assertSame(true, $adprovider->hasZone($site, 'foo'));
        $this->assertSame(false, $adprovider->hasZone($site, 'bar'));
    }
}

class CM_AdproviderAdapter_Mock extends CM_AdproviderAdapter_Abstract {

    public function getHtml($zoneName, array $zoneData, array $variables = null) {
        return json_encode(array('zoneData' => $zoneData, 'variables' => $variables));
    }
}
