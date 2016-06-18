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

    public function testGetPath() {
        $urlParser = new CM_Http_UrlParser('http://www.example.com/foo?bar=12');
        $this->assertSame('/foo', $urlParser->getPath());
    }

    public function testGetPathEmpty() {
        $urlParser = new CM_Http_UrlParser('http://www.example.com');
        $this->assertSame('/', $urlParser->getPath());
    }

}
