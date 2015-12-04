<?php

class CM_AdproviderAdapter_IframeTest extends CMTest_TestCase {

    public function testGetHtml() {
        $provider = new CM_AdproviderAdapter_Iframe();

        $html = $provider->getHtml('zoneName1', [
            'src'    => 'http://example.com',
            'width'  => 200,
            'height' => 100,
        ], [
            'foo' => 'bar',
        ]);

        $this->assertSame(
            '<iframe src="http://example.com" width="200" height="100" class="advertisement-hasContent" data-variables="{&quot;foo&quot;:&quot;bar&quot;}" frameborder="0" scrolling="no"></iframe>'
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
