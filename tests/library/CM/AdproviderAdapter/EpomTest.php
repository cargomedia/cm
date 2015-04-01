<?php

class CM_AdproviderAdapter_EpomTest extends CMTest_TestCase {

    public function testGetHtml() {
        $epom = new CM_AdproviderAdapter_Epom();

        $html = $epom->getHtml(array('zoneId' => 'zone1', 'accessKey' => 'accessKey'), array('foo' => 'bar'));
        $this->assertContains('<div id="epom-zone1" class="epom-ad" data-zone-id="zone1" data-variables="{&quot;foo&quot;:&quot;bar&quot;,&quot;key&quot;:&quot;accessKey&quot;}"', $html);

        $html = $epom->getHtml(array('zoneId' => 'zone1', 'accessKey' => 'accessKey'), array());
        $this->assertContains('<div id="epom-zone1" class="epom-ad" data-zone-id="zone1" data-variables="{&quot;key&quot;:&quot;accessKey&quot;}"', $html);
    }
}
