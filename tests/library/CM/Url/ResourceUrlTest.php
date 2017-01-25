<?php

namespace CM\Test\Url;

use CMTest_TH;
use CMTest_TestCase;
use CM\Url\ResourceUrl;

class ResourceUrlTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        $url = ResourceUrl::create('file.ext', 'resource-type');
        $this->assertSame('/resource-type/file.ext', (string) $url);

        $language = CMTest_TH::createLanguage('de');
        $url = ResourceUrl::create('file.ext', 'resource-type', $language);
        $this->assertSame('/resource-type/de/file.ext', (string) $url);

        $url = ResourceUrl::create('file.ext', 'resource-type', $language, 1234);
        $this->assertSame('/resource-type/de/1234/file.ext', (string) $url);
    }

    public function testWithSite() {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\CM_Site_Abstract $site */
        $site = $this
            ->getMockBuilder('CM_Site_Abstract')
            ->setMethods(['getId', 'getUrlBase', 'getUrlCdn'])
            ->getMockForAbstractClass();

        $site
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(42));

        $site
            ->expects($this->any())
            ->method('getUrlBase')
            ->will($this->returnValue('http://foo/path?param'));

        $site
            ->expects($this->any())
            ->method('getUrlCdn')
            ->will($this->returnValue('http://cdn.foo/path?param'));

        $language = CMTest_TH::createLanguage('de');
        $url = ResourceUrl::create('file.ext', 'resource-type', $language, 1234);

        $urlWithSite = $url->withSite($site);
        $this->assertSame('http://cdn.foo/resource-type/de/42/1234/file.ext', (string) $urlWithSite);

        $urlWithSiteSameOrigin = $url->withSite($site, true);
        $this->assertSame('http://foo/resource-type/de/42/1234/file.ext', (string) $urlWithSiteSameOrigin);
    }
}
