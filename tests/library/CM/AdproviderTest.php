<?php

class CM_AdproviderTest extends CMTest_TestCase {

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testGetHtml() {
        $site = $this->getMockSite();

        $zones = array('foo' => array('adapter' => 'CM_AdproviderAdapter_Mock', 'zoneId' => 1),);
        $adproviderConfig = array('CM_AdproviderAdapter_Mock' => []);
        $adprovider = new CM_Adprovider(true, $zones, $adproviderConfig);

        $this->assertSame('{"zoneData":{"zoneId":1},"variables":[]}', $adprovider->getHtml($site, 'foo'));
        $this->assertSame('{"zoneData":{"zoneId":1},"variables":{"foo":"bar"}}', $adprovider->getHtml($site, 'foo', array('foo' => 'bar')));

        $adproviderDisabled = new CM_Adprovider(false, $zones, $adproviderConfig);
        $this->assertSame('', $adproviderDisabled->getHtml($site, 'foo'));
    }

    public function testGetHtmlWithSiteConfig() {
        $zones = array('foo' => array('adapter' => 'CM_AdproviderAdapter_Mock', 'zoneId' => 1),);
        $adproviderConfig = array('CM_AdproviderAdapter_Mock' => []);
        $adprovider = new CM_Adprovider(true, $zones, $adproviderConfig);

        $site = $this->getMockSite();
        $siteClassName = get_class($site);
        CM_Config::get()->$siteClassName->CM_Adprovider = new stdClass();
        CM_Config::get()->$siteClassName->CM_Adprovider->zones = array('foo' => array('adapter' => 'CM_AdproviderAdapter_Mock', 'zoneId' => 2),);

        $this->assertSame('{"zoneData":{"zoneId":2},"variables":[]}', $adprovider->getHtml($site, 'foo'));
        $this->assertSame('{"zoneData":{"zoneId":2},"variables":{"foo":"bar"}}', $adprovider->getHtml($site, 'foo', array('foo' => 'bar')));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Ad adapter `CM_AdproviderAdapter_Nonexistent` is not configured
     */
    public function testGetHtmlInvalidAdapter() {
        $site = $this->getMockSite();

        $zones = array('foo' => array('adapter' => 'CM_AdproviderAdapter_Nonexistent'),);
        $adprovider = new CM_Adprovider(true, $zones, []);
        $adprovider->getHtml($site, 'foo');
    }
}

class CM_AdproviderAdapter_Mock extends CM_AdproviderAdapter_Abstract {

    public function getHtml($zoneData, array $variables) {
        return json_encode(array('zoneData' => $zoneData, 'variables' => $variables));
    }
}
