<?php

class CM_AdproviderAdapter_IframeTest extends CMTest_TestCase {

    public function testGetHtml() {
        $provider = new CM_AdproviderAdapter_Iframe();

        $html = $provider->getHtml('zoneName1', [
            'src'    => 'http://example.com',
            'width'  => '100%',
            'height' => 100,
        ], [
            'foo' => 'bar',
        ]);

        $this->assertSame(
            '<iframe src="http://example.com" width="100%" height="100" class="Adv3rt153m3nt-hasContent" frameborder="0" scrolling="no" data-variables="{&quot;foo&quot;:&quot;bar&quot;}"></iframe>'
            , $html);
    }

    /**
     * @expectedException ErrorException
     * @expectedExceptionMessage Undefined index
     */
    public function testGetHtmlMissingParams() {
        $provider = new CM_AdproviderAdapter_Iframe();

        $provider->getHtml('zoneName1', [], []);
    }
}
