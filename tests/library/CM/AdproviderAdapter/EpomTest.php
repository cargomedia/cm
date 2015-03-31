<?php

class CM_AdproviderAdapter_EpomTest extends CMTest_TestCase {

    public function testGetHtml() {
        CM_Config::get()->CM_Adprovider->enabled = true;
        CM_Config::get()->CM_AdproviderAdapter_Epom = array('accessKey' => 'accessKey');
        $epom = new CM_AdproviderAdapter_Epom();

        $html = $epom->getHtml(array('zoneId' => 'zone1'), array('foo' => 'bar'));
        $this->assertContains('<div id="epom-zone1" class="epom-ad" data-zone-id="zone1" data-variables="{&quot;foo&quot;:&quot;bar&quot;,&quot;key&quot;:&quot;accessKey&quot;}"', $html);

        $html = $epom->getHtml(array('zoneId' => 'zone1'), array());
        $this->assertContains('<div id="epom-zone1" class="epom-ad" data-zone-id="zone1" data-variables="{&quot;key&quot;:&quot;accessKey&quot;}"', $html);
    }
}
