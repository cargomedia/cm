<?php

namespace CM\Test\Url;

use CMTest_TH;
use CMTest_TestCase;
use CM\Url\ServiceWorkerUrl;

class ServiceWorkerUrlTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        $url = ServiceWorkerUrl::create();
        $this->assertSame('/serviceworker.js', (string) $url);

        $environment = $this->createEnvironment();
        $url = ServiceWorkerUrl::create($environment);
        $this->assertSame('http://www.example.com/serviceworker.js', (string) $url);

        $environment = $this->createEnvironment(['url' => 'http://www.example.com/prefix']);
        $url = ServiceWorkerUrl::create($environment);
        $this->assertSame('http://www.example.com/prefix/serviceworker.js', (string) $url);

        $environment = $this->createEnvironment(['url' => 'http://www.example.com/prefix'], null, 'de');
        $url = ServiceWorkerUrl::create($environment);
        $this->assertSame('http://www.example.com/prefix/serviceworker-de.js', (string) $url);

        $url = ServiceWorkerUrl::create($environment, 123);
        $this->assertSame('http://www.example.com/prefix/serviceworker-de-123.js', (string) $url);
    }

    public function testMatchUri() {
        $this->assertSame(true, ServiceWorkerUrl::matchUri('/serviceworker.js'));
        $this->assertSame(true, ServiceWorkerUrl::matchUri('/serviceworker-foo-123.js'));
        $this->assertSame(true, ServiceWorkerUrl::matchUri('http://example.com/serviceworker.js?foo=bar#fragment'));
        $this->assertSame(true, ServiceWorkerUrl::matchUri('http://example.com/prefix/serviceworker.js?foo=bar#fragment'));
        $this->assertSame(false, ServiceWorkerUrl::matchUri('serviceworker.js'));
        $this->assertSame(false, ServiceWorkerUrl::matchUri('/serviceworker-123-foo.js'));
        $this->assertSame(false, ServiceWorkerUrl::matchUri('/service.js'));
        $this->assertSame(false, ServiceWorkerUrl::matchUri('/serviceworker/foo.js'));
        $this->assertSame(false, ServiceWorkerUrl::matchUri('/serviceworker foo.js'));
    }
}
