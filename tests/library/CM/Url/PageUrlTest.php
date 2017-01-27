<?php

namespace CM\Test\Url;

use CMTest_TH;
use CMTest_TestCase;
use CM\Url\BaseUrl;
use CM\Url\PageUrl;

class PageUrlTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {

        $url = PageUrl::create(\CM_Page_Error_NotFound::class);
        $this->assertSame('/error/not-found', (string) $url);

        $url = PageUrl::create(\CM_Page_Example::class);
        $this->assertSame('/example', (string) $url);

        $url = PageUrl::create(\CM_Page_Example::class, ['foo' => 'bar']);
        $this->assertSame('/example?foo=bar', (string) $url);

        $environment = $this->createEnvironment();
        $url = PageUrl::create(\CM_Page_Example::class, ['foo' => 'bar'], $environment);
        $this->assertSame('http://www.example.com/example?foo=bar', (string) $url);

        $environment = $this->createEnvironment(null, null, 'de');
        $url = PageUrl::create(\CM_Page_Example::class, ['foo' => 'bar'], $environment);
        $this->assertSame('http://www.example.com/de/example?foo=bar', (string) $url);

        /** @var \CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () {
            PageUrl::create('CM_Page_Foo');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Failed to create PageUrl, page class does not exist', $exception->getMessage());
        $this->assertSame(['pageClassName' => 'CM_Page_Foo'], $exception->getMetaInfo());
    }
}
