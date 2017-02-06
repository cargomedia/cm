<?php

class CM_AdproviderAdapter_ReviveTest extends CMTest_TestCase {

    public function testGetHtml() {
        CM_Config::get()->CM_Adprovider->enabled = true;
        CM_Config::get()->CM_AdproviderAdapter_Revive->host = 'www.foo.org';
        $revive = new CM_AdproviderAdapter_Revive();

        $html = $revive->getHtml('zoneName1', array('zoneId' => 'zone1'), array('foo' => 'bar'));
        $this->assertContains('<div class="revive-ad" data-zone-id="zone1" data-host="www.foo.org" data-variables="{&quot;foo&quot;:&quot;bar&quot;}"', $html);

        $html = $revive->getHtml('zoneName1', array('zoneId' => 'zone1'), array());
        $this->assertContains('<div class="revive-ad" data-zone-id="zone1" data-host="www.foo.org" data-variables="{}"', $html);
    }
}
