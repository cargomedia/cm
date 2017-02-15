<?php

namespace CM\Test\Url;

use CMTest_TH;
use CMTest_TestCase;
use CM_Frontend_Environment;
use CM\Url\PageUrl;

class PageUrlTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {

        $url = PageUrl::create(\CM_Page_Error_NotFound::class);
        $this->assertSame('CM_Page_Error_NotFound', $url->getPageClassName());
        $this->assertSame('/error/not-found', (string) $url);

        $url = PageUrl::create(\CM_Page_Example::class);
        $this->assertSame('CM_Page_Example', $url->getPageClassName());
        $this->assertSame('/example', (string) $url);

        $page = new \CM_Page_Example();
        $url = PageUrl::create($page);
        $this->assertSame('CM_Page_Example', $url->getPageClassName());
        $this->assertSame('/example', (string) $url);

        $url = PageUrl::create(\CM_Page_Example::class, ['foo' => 'bar']);
        $this->assertSame('CM_Page_Example', $url->getPageClassName());
        $this->assertSame('/example?foo=bar', (string) $url);

        $environment = $this->createEnvironment();
        $url = PageUrl::create(\CM_Page_Example::class, ['foo' => 'bar'], $environment);
        $this->assertSame('CM_Page_Example', $url->getPageClassName());
        $this->assertSame('http://www.example.com/example?foo=bar', (string) $url);

        $environment = $this->createEnvironment(null, null, 'de');
        $url = PageUrl::create(\CM_Page_Example::class, ['foo' => 'bar'], $environment);
        $this->assertSame('CM_Page_Example', $url->getPageClassName());
        $this->assertSame('http://www.example.com/de/example?foo=bar', (string) $url);

        /** @var \CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () {
            PageUrl::create('CM_Page_Foo');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Cannot find valid class definition for page class name', $exception->getMessage());
        $this->assertSame(['pageClassName' => 'CM_Page_Foo'], $exception->getMetaInfo());

        /** @var \CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () {
            $url = PageUrl::create('CM_Page_Example');
            $url->withPath('/foo/bar');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Cannot find valid class definition for page class name', $exception->getMessage());
        $this->assertSame(['pageClassName' => '/foo/bar'], $exception->getMetaInfo());

        /** @var \PHPUnit_Framework_MockObject_MockObject|\CM_Site_Abstract $site */
        $site = $this
            ->getMockBuilder('CM_Site_Abstract')
            ->setMethods(['getModules'])
            ->getMockForAbstractClass();
        $site
            ->expects($this->exactly(2))
            ->method('getModules')
            ->will($this->returnValue([]));
        $url = PageUrl::create('CM_Page_Example');

        /** @var \CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($url, $site) {
            $url->withSite($site);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Site does not contain namespace', $exception->getMessage());
        $this->assertSame([
            'site'      => get_class($site),
            'namespace' => 'CM',
        ], $exception->getMetaInfo());

        /** @var \CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($url, $site) {
            $environment = new CM_Frontend_Environment($site);
            $url->withEnvironment($environment);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Site does not contain namespace', $exception->getMessage());
        $this->assertSame([
            'site'      => get_class($site),
            'namespace' => 'CM',
        ], $exception->getMetaInfo());
    }
}
