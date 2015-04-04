<?php

class CM_Http_Response_Resource_Layout_RootTest extends CMTest_TestCase {

    public function testMarshalling(){
        $path = 'foo/bar.png';

        $pathMarshalled = CM_Http_Response_Resource_Layout_Root::marshalPath('foo/bar.png');
        $this->assertSame('/resource-layout-foo--bar.png', $pathMarshalled);

        $pathUnmarshalled = CM_Http_Response_Resource_Layout_Root::unmarshalPath($pathMarshalled);
        $this->assertSame($path, $pathUnmarshalled);
    }

    /**
     * @expectedException CM_Exception_Invalid
     */
    public function testMarshallingUnsupported(){
        CM_Http_Response_Resource_Layout_Root::marshalPath('foo--bar.png');
    }

    public function testProcess() {
        $request = $this->createRequest('/resource-layout-img--logo.png');
        $response = $this->processRequest($request);

        $this->assertContains('Content-Type: image/png', $response->getHeaders());
    }
}
