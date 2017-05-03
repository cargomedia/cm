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

    public function testCreate() {
        $url = RouteUrl::create('some-action');
        $this->assertSame('/some-action', (string) $url);

        $url = RouteUrl::create('some-action', ['foo' => 'bar']);
        $this->assertSame('/some-action?foo=bar', (string) $url);

        $environment = $this->createEnvironment();
        $url = RouteUrl::create('some-action', ['foo' => 'bar'], $environment);
        $this->assertSame('http://www.example.com/site-42/some-action?foo=bar', (string) $url);

        $environment = $this->createEnvironment(null, null, 'de');
        $url = RouteUrl::create('some-action', ['foo' => 'bar'], $environment);
        $this->assertSame('http://www.example.com/language-de/site-43/some-action?foo=bar', (string) $url);
    }
}
