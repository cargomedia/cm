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

        $environment = $this->createEnvironment();
        $url = StaticUrl::create('file.ext', $environment);
        $this->assertSame('http://cdn.example.com/static/file.ext', (string) $url);

        $environment = $this->createEnvironment(['url' => 'http://www.example.com/prefix']);
        $url = StaticUrl::create('file.ext', $environment);
        $this->assertSame('http://cdn.example.com/static/file.ext', (string) $url);

        $url = StaticUrl::create('file.ext', $environment, 1234);
        $this->assertSame('http://cdn.example.com/static/file.ext?1234', (string) $url);

        $url = StaticUrl::create('file.ext?foo=bar', $environment, 1234);
        $this->assertSame('http://cdn.example.com/static/file.ext?foo=bar&1234', (string) $url);
    }
}
