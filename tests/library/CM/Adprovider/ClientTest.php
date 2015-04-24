<?php

class CM_Adprovider_ClientTest extends CMTest_TestCase {

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testGetHtml() {
        $site = $this->getMockSite();
        $adapter = $this->mockObject('CM_Adprovider_Adapter_Abstract');
        $adapter->mockMethod('getHtml')->set(function($zoneData, array $variables) {
            return json_encode(array('zoneData' => $zoneData, 'variables' => $variables));
        });
        /** @var CM_Adprovider_Adapter_Abstract $adapter */

        $zones = array('foo' => array('adapter' => get_class($adapter), 'zoneId' => 1),);
        $adprovider = new CM_Adprovider_Client(true, $zones);
        $adprovider->addAdapter($adapter);

        $this->assertSame('{"zoneData":{"zoneId":1},"variables":[]}', $adprovider->getHtml($site, 'foo'));
        $this->assertSame('{"zoneData":{"zoneId":1},"variables":{"foo":"bar"}}', $adprovider->getHtml($site, 'foo', array('foo' => 'bar')));

        $adproviderDisabled = new CM_Adprovider_Client(false, $zones);
        $adproviderDisabled->addAdapter($adapter);
        $this->assertSame('', $adproviderDisabled->getHtml($site, 'foo'));
    }

    public function testGetHtmlWithSiteConfig() {
        $adapter = $this->mockObject('CM_Adprovider_Adapter_Abstract');
        $adapter->mockMethod('getHtml')->set(function($zoneData, array $variables) {
            return json_encode(array('zoneData' => $zoneData, 'variables' => $variables));
        });
        /** @var CM_Adprovider_Adapter_Abstract $adapter */

        $zones = array('foo' => array('adapter' => get_class($adapter), 'zoneId' => 1),);
        $adprovider = new CM_Adprovider_Client(true, $zones);
        $adprovider->addAdapter($adapter);

        $site = $this->getMockSite();
        $siteClassName = get_class($site);
        CM_Config::get()->$siteClassName->CM_Adprovider = new stdClass();
        CM_Config::get()->$siteClassName->CM_Adprovider->zones = array('foo' => array('adapter' => get_class($adapter), 'zoneId' => 2),);

        $this->assertSame('{"zoneData":{"zoneId":2},"variables":[]}', $adprovider->getHtml($site, 'foo'));
        $this->assertSame('{"zoneData":{"zoneId":2},"variables":{"foo":"bar"}}', $adprovider->getHtml($site, 'foo', array('foo' => 'bar')));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Adprovider adapter `CM_Adprovider_Adapter_Nonexistent` not found
     */
    public function testGetHtmlInvalidAdapter() {
        $site = $this->getMockSite();

        $zones = array('foo' => array('adapter' => 'CM_Adprovider_Adapter_Nonexistent'),);
        $adprovider = new CM_Adprovider_Client(true, $zones, []);
        $adprovider->getHtml($site, 'foo');
    }
}
