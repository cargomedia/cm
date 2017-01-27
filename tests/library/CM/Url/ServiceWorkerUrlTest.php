<?php

namespace CM\Test\Url;

use CMTest_TH;
use CMTest_TestCase;
use CM\Url\BaseUrl;
use CM\Url\ServiceWorkerUrl;

class ServiceWorkerUrlTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        $url = ServiceWorkerUrl::create();
        $this->assertSame('/serviceworker.js', (string) $url);

        $url = ServiceWorkerUrl::create('foo');
        $this->assertSame('/foo.js', (string) $url);

        $environment = $this->createEnvironment();
        $url = ServiceWorkerUrl::create('foo', $environment);
        $this->assertSame('http://www.example.com/foo.js', (string) $url);

        $environment = $this->createEnvironment(['url' => 'http://www.example.com/prefix']);
        $url = ServiceWorkerUrl::create('foo', $environment);
        $this->assertSame('http://www.example.com/prefix/foo.js', (string) $url);

        $environment = $this->createEnvironment(['url' => 'http://www.example.com/prefix'], null, 'de');
        $url = ServiceWorkerUrl::create('foo', $environment);
        $this->assertSame('http://www.example.com/prefix/foo-de.js', (string) $url);

        $url = ServiceWorkerUrl::create('foo', $environment, 123);
        $this->assertSame('http://www.example.com/prefix/foo-de-123.js', (string) $url);
    }
}
