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

        $environment = $this->createEnvironment();
        $url = RouteUrl::create('some-action', ['foo' => 'bar'], $environment);
        $this->assertSame('http://www.example.com/some-action?foo=bar', (string) $url);

        $environment = $this->createEnvironment(null, null, 'de');
        $url = RouteUrl::create('some-action', ['foo' => 'bar'], $environment);
        $this->assertSame('http://www.example.com/some-action/de?foo=bar', (string) $url);
    }
}
