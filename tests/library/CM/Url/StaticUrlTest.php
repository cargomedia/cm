<?php

namespace CM\Test\Url;

use CMTest_TH;
use CMTest_TestCase;
use CM\Url\StaticUrl;

class StaticUrlTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        $url = StaticUrl::create('file.ext');
        $this->assertSame('/static/file.ext', (string) $url);

        $baseUrl = StaticUrl::createFromString('http://host');
        $url = StaticUrl::create('file.ext', $baseUrl);
        $this->assertSame('http://host/static/file.ext', (string) $url);

        $baseUrlWithPrefix = $baseUrl->withPrefix('prefix');
        $url = StaticUrl::create('file.ext', $baseUrlWithPrefix);
        $this->assertSame('http://host/static/file.ext', (string) $url);

        $url = StaticUrl::create('file.ext', $baseUrl, 1234);
        $this->assertSame('http://host/static/file.ext?1234', (string) $url);

        $url = StaticUrl::create('file.ext?foo=bar', $baseUrl, 1234);
        $this->assertSame('http://host/static/file.ext?foo=bar&1234', (string) $url);
    }
}
