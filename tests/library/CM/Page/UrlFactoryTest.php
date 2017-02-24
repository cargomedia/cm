<?php

use League\Uri\Components\Query;

class CM_Page_UrlFactoryTest extends CMTest_TestCase {

    public function testGetUrl() {
        $url = CM_Page_UrlFactory::getUrl(CM_Page_Error_NotFound::class);
        $this->assertSame('/error/not-found', (string) $url);

        $url = CM_Page_UrlFactory::getUrl(CM_Page_Example::class);
        $this->assertSame('/example', (string) $url);

        $url = CM_Page_UrlFactory::getUrl(CM_Page_Example::class, ['foo' => 'bar']);
        $this->assertSame('/example?foo=bar', (string) $url);

        $url = CM_Page_UrlFactory::getUrl(CM_Page_UrlFactory_Mock::class, ['foo' => 'bar']);
        $this->assertSame('/foo?foo=bar&mock=1#bar', (string) $url);

        $environment = $this->createEnvironment();
        $url = CM_Page_UrlFactory::getUrl(CM_Page_Example::class, ['foo' => 'bar'], $environment);
        $this->assertSame('http://www.example.com/example?foo=bar', (string) $url);

        $environment = $this->createEnvironment(null, null, 'de');
        $url = CM_Page_UrlFactory::getUrl(CM_Page_Example::class, ['foo' => 'bar'], $environment);
        $this->assertSame('http://www.example.com/de/example?foo=bar', (string) $url);

        $url = CM_Page_UrlFactory::getUrl(CM_Page_UrlFactory_Mock::class, ['foo' => 'bar'], $environment);
        $this->assertSame('http://www.example.com/de/foo?foo=bar&mock=1#bar', (string) $url);

        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () {
            CM_Page_UrlFactory::getUrl('CM_Page_Foo');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Cannot find valid class definition for page class name', $exception->getMessage());
        $this->assertSame(['pageClassName' => 'CM_Page_Foo'], $exception->getMetaInfo());

        /** @var \PHPUnit_Framework_MockObject_MockObject|CM_Site_Abstract $site */
        $site = $this
            ->getMockBuilder('CM_Site_Abstract')
            ->setMethods(['getModules'])
            ->getMockForAbstractClass();
        $site
            ->expects($this->any())
            ->method('getModules')
            ->will($this->returnValue([]));

        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($url, $site) {
            $environment = new CM_Frontend_Environment($site);
            CM_Page_UrlFactory::getUrl(CM_Page_Example::class, [], $environment);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Site does not contain namespace', $exception->getMessage());
        $this->assertSame([
            'site'      => get_class($site),
            'namespace' => 'CM',
        ], $exception->getMetaInfo());
    }

    public function testAssertPage() {
        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () {
            CM_Page_UrlFactory::assertPage('CM_Page_Foo');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Cannot find valid class definition for page class name', $exception->getMessage());
        $this->assertSame(['pageClassName' => 'CM_Page_Foo'], $exception->getMetaInfo());
    }

    public function testAssertSupportedSite() {
        /** @var \PHPUnit_Framework_MockObject_MockObject|CM_Site_Abstract $site */
        $site = $this
            ->getMockBuilder('CM_Site_Abstract')
            ->setMethods(['getModules'])
            ->getMockForAbstractClass();
        $site
            ->expects($this->any())
            ->method('getModules')
            ->will($this->returnValue([]));

        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($site) {
            CM_Page_UrlFactory::assertSupportedSite('Foo', $site);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Cannot find namespace of page class name', $exception->getMessage());
        $this->assertSame([
            'pageClassName' => 'Foo',
        ], $exception->getMetaInfo());

        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($site) {
            CM_Page_UrlFactory::assertSupportedSite('CM_Page_Example', $site);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Site does not contain namespace', $exception->getMessage());
        $this->assertSame([
            'site'      => get_class($site),
            'namespace' => 'CM',
        ], $exception->getMetaInfo());
    }
}

class CM_Page_UrlFactory_Mock extends CM_Page_Abstract {

    public static function getUrlComponents(array $params = null) {
        return [
            'path'     => '/foo',
            'query'    => (string) Query::createFromPairs(array_merge($params, ['mock' => 1])),
            'fragment' => 'bar'
        ];
    }
}
