<?php

class CM_Adprovider_Adapter_OpenxTest extends CMTest_TestCase {

    public function testGetHtml() {
        $openx = new CM_Adprovider_Adapter_Openx('www.foo.org');

        $html = $openx->getHtml(array('zoneId' => 'zone1'), array('foo' => 'bar'));
        $this->assertContains('<div class="openx-ad" data-zone-id="zone1" data-host="www.foo.org" data-variables="{&quot;foo&quot;:&quot;bar&quot;}"', $html);

        $html = $openx->getHtml(array('zoneId' => 'zone1'), array());
        $this->assertContains('<div class="openx-ad" data-zone-id="zone1" data-host="www.foo.org" data-variables="{}"', $html);
    }
}
