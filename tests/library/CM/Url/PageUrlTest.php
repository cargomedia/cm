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

        $baseUrl = BaseUrl::create('http://host');
        $url = PageUrl::create(\CM_Page_Example::class, ['foo' => 'bar'], $baseUrl);
        $this->assertSame('http://host/example?foo=bar', (string) $url);

        $language = CMTest_TH::createLanguage('de');
        $url = PageUrl::create(\CM_Page_Example::class, ['foo' => 'bar'], $baseUrl, $language);
        $this->assertSame('http://host/de/example?foo=bar', (string) $url);

        /** @var \CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () {
            PageUrl::create('CM_Page_Foo');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Failed to create PageUrl, page class does not exist', $exception->getMessage());
        $this->assertSame(['pageClassName' => 'CM_Page_Foo'], $exception->getMetaInfo());
    }
}
