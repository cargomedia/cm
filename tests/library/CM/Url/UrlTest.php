<?php

namespace CM\Test\Url;

use CMTest_TH;
use CMTest_TestCase;
use CM\Url\Url;

class UrlTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        $url = Url::create('');
        $this->assertSame('/', $url->getPath());
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame('/', (string) $url);

        $url = Url::create('http://host');
        $this->assertSame('/', $url->getPath());
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame('http://host/', (string) $url);

        $environment = $this->createEnvironment();
        $url = Url::create('path', $environment);
        $this->assertSame('/path', $url->getPath());
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame('http://www.example.com/path', (string) $url);

        $environment = $this->createEnvironment(['url' => 'http://www.example.com/prefix']);
        $url = Url::create('path/to/something?param=1#fragment', $environment);
        $this->assertSame('/path/to/something', $url->getPath());
        $this->assertSame('prefix', $url->getPrefix());
        $this->assertSame('http://www.example.com/prefix/path/to/something?param=1#fragment', (string) $url);

        $environment = $this->createEnvironment(['url' => 'http://www.example.com/foo']);
        $url = $url->withSite($environment->getSite());
        $this->assertSame('http://www.example.com/foo/path/to/something?param=1#fragment', (string) $url);
    }
}
