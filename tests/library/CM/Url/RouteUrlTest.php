<?php

namespace CM\Test\Url;

use CMTest_TH;
use CMTest_TestCase;
use CM\Url\BaseUrl;
use CM\Url\RouteUrl;

class RouteUrlTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testWithParams() {
        $url = RouteUrl::createFromString('/foo');

        $this->assertSame('/foo', (string) $url);
        $this->assertSame(null, $url->getParams());

        $url = $url->withParams(['foo', 123 => null, 'bar' => 'baz']);

        $this->assertSame('/foo?0=foo&123&bar=baz', (string) $url);
        $this->assertSame(['foo', 123 => null, 'bar' => 'baz'], $url->getParams());
    }

    public function testCreate() {
        $url = RouteUrl::create('some-action');
        $this->assertSame('/some-action', (string) $url);

        $url = RouteUrl::create('some-action', ['foo' => 'bar']);
        $this->assertSame('/some-action?foo=bar', (string) $url);

        $baseUrl = BaseUrl::create('http://host');
        $url = RouteUrl::create('some-action', ['foo' => 'bar'], $baseUrl);
        $this->assertSame('http://host/some-action?foo=bar', (string) $url);

        $language = CMTest_TH::createLanguage('de');
        $url = RouteUrl::create('some-action', ['foo' => 'bar'], $baseUrl, $language);
        $this->assertSame('http://host/some-action/de?foo=bar', (string) $url);
    }
}
