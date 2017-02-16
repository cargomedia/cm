<?php

class CM_Http_ClientDeviceTest extends CMTest_TestCase {

    /** @var array */
    private $_mobileHeaders;

    /** @var array */
    private $_nonMobileHeaders;

    public function setUp() {
        $this->_nonMobileHeaders = [
            'content-type'              => '',
            'content-length'            => '',
            'host'                      => 'dev.cm',
            'connection'                => 'keep-alive',
            'cache-control'             => 'max-age=0',
            'accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'upgrade-insecure-requests' => '1',
            'user-agent'                => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.73 Safari/537.36',
            'accept-encoding'           => 'gzip, deflate, sdch',
            'accept-language'           => 'en-US,en;q=0.8,de-CH;q=0.6,de;q=0.4,fr;q=0.2,it;q=0.2,pt;q=0.2,es;q=0.2',
            'cookie'                    => '__utma=12212262.646672065.1416384804.1441359798.1442238760.18; __utmc=12212262; __utmz=12212262.1437935861.16.3.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=(not%20provided);',
        ];

        $this->_mobileHeaders = [
            'content-type'              => '',
            'content-length'            => '',
            'host'                      => 'dev.cm',
            'connection'                => 'keep-alive',
            'cache-control'             => 'max-age=0',
            'accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'upgrade-insecure-requests' => '1',
            'user-agent'                => 'Mozilla/5.0 (Linux; Android 5.0.2; SAMSUNG SM-G925F Build/LRX22G) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/3.0 Chrome/38.0.2125.102 Mobile Safari/537.36',
            'accept-encoding'           => 'gzip, deflate, sdch',
            'accept-language'           => 'en-US,en;q=0.8,de-CH;q=0.6,de;q=0.4,fr;q=0.2,it;q=0.2,pt;q=0.2,es;q=0.2',
        ];
    }

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testIsMobile() {
        $clientDeviceDetector = new CM_Http_ClientDevice(CM_Http_Request_Abstract::factory('get', '/foo', $this->_nonMobileHeaders));

        $this->assertInstanceOf('CM_Http_ClientDevice', $clientDeviceDetector);
        $this->assertFalse($clientDeviceDetector->isMobile());

        $clientDeviceDetector = new CM_Http_ClientDevice(CM_Http_Request_Abstract::factory('post', '/bar', $this->_mobileHeaders));
        $this->assertTrue($clientDeviceDetector->isMobile());
    }

    public function testGetVersion() {
        $clientDeviceDetector = new CM_Http_ClientDevice(CM_Http_Request_Abstract::factory('get', '/foo', $this->_nonMobileHeaders));

        $this->assertInstanceOf('CM_Http_ClientDevice', $clientDeviceDetector);
        $this->assertFalse($clientDeviceDetector->getVersion('Android'));

        $clientDeviceDetector = new CM_Http_ClientDevice(CM_Http_Request_Abstract::factory('post', '/bar', $this->_mobileHeaders));
        $this->assertSame('5.0.2', $clientDeviceDetector->getVersion('Android'));
    }

    public function testGetIP() {
        $clientInfo = new CM_Http_ClientInfo(CM_Http_Request_Abstract::factory('get', '/foo', null, []));
        $this->assertNull($clientInfo->getIp());

        $clientInfo = new CM_Http_ClientInfo(CM_Http_Request_Abstract::factory('get', '/foo', null, ['remote_addr' => '42.42.42.42']));
        $this->assertSame('42.42.42.42', $clientInfo->getIp());
    }
}
