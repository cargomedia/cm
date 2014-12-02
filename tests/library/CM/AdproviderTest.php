<?php

class CM_AdproviderTest extends CMTest_TestCase {

    protected function tearDown() {
        CMTest_TH::clearConfig();
        CMTest_TH::clearCache();
    }

    public function testGetHtml() {
        $site = $this->getMockSite();

        CM_Config::get()->CM_Adprovider->enabled = true;
        CM_Config::get()->CM_Adprovider->zones = array('foo' => array('adapter' => 'CM_AdproviderAdapter_Mock', 'zoneId' => 1),);
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
        CM_Config::get()->CM_Adprovider->zones = array('foo' => array('adapter' => 'CM_AdproviderAdapter_Mock', 'zoneId' => 1),);
        CM_Config::get()->$siteClassName->CM_Adprovider = new stdClass();
        CM_Config::get()->$siteClassName->CM_Adprovider->zones = array('foo' => array('adapter' => 'CM_AdproviderAdapter_Mock', 'zoneId' => 2),);
        $adprovider = new CM_Adprovider();

        $this->assertSame('{"zoneData":{"zoneId":2},"variables":[]}', $adprovider->getHtml($site, 'foo'));
        $this->assertSame('{"zoneData":{"zoneId":2},"variables":{"foo":"bar"}}', $adprovider->getHtml($site, 'foo', array('foo' => 'bar')));

        CM_Config::get()->CM_Adprovider->enabled = false;
        $this->assertSame('', $adprovider->getHtml($site, 'foo'));
    }

    public function testGetHtmlInvalidAdapter() {
        $site = $this->getMockSite();

        CM_Config::get()->CM_Adprovider->enabled = true;
        CM_Config::get()->CM_Adprovider->zones = array('foo' => array('adapter' => 'CM_AdproviderAdapter_Nonexistent'),);
        $adprovider = new CM_Adprovider();

        try {
            $adprovider->getHtml($site, 'foo');
            $this->fail('No exception for invalid ad adapter');
        } catch (CM_Exception_Invalid $e) {
            $this->assertContains('Invalid ad adapter', $e->getMessage());
        }
    }
}

class CM_AdproviderAdapter_Mock extends CM_AdproviderAdapter_Abstract {

    public function getHtml($zoneData, array $variables) {
        return json_encode(array('zoneData' => $zoneData, 'variables' => $variables));
    }
}
