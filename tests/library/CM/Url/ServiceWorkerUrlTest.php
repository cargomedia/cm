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

        $baseUrl = BaseUrl::create('http://host');
        $url = ServiceWorkerUrl::create('foo', $baseUrl);
        $this->assertSame('http://host/foo.js', (string) $url);

        $baseUrlWithPrefix = $baseUrl->withPrefix('prefix');
        $url = ServiceWorkerUrl::create('foo', $baseUrlWithPrefix);
        $this->assertSame('http://host/prefix/foo.js', (string) $url);

        $language = CMTest_TH::createLanguage('de');
        $url = ServiceWorkerUrl::create('foo', $baseUrlWithPrefix, $language);
        $this->assertSame('http://host/prefix/foo-de.js', (string) $url);

        $url = ServiceWorkerUrl::create('foo', $baseUrlWithPrefix, $language, 123);
        $this->assertSame('http://host/prefix/foo-de-123.js', (string) $url);
    }
}
