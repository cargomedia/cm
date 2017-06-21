<?php

class CM_AdproviderAdapter_Revive_IframeTest extends CMTest_TestCase {

    public function testGetHtml() {
        $provider = new CM_AdproviderAdapter_Revive_Iframe();
        mt_srand(0);

        $html = $provider->getHtml('zoneName1', [
            'zoneId' => 1,
            'host'   => 'example.com',
            'width'  => '100%',
            'height' => 100,
        ], [
            'foo' => 'bar',
        ]);

        $this->assertSame(
            '<iframe src="//example.com/delivery/afr.php?foo=bar&amp;zoneid=1&amp;cb=963932192" width="100%" height="100" class="Adv3rt153m3nt-hasContent" frameborder="0" scrolling="no" data-variables="{}"></iframe>'
            , $html);
    }
}
