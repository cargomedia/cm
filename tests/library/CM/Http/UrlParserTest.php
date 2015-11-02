<?php

class CM_Http_UrlParserTest extends CMTest_TestCase {

    public function testGetHost() {
        $urlParser = new CM_Http_UrlParser('http://www.example.com');
        $this->assertSame('www.example.com', $urlParser->getHost());
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot detect host
     */
    public function testGetHostInvalid() {
        $urlParser = new CM_Http_UrlParser('jo');
        $urlParser->getHost();
    }

}
