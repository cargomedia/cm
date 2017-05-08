<?php

namespace CM\Test\Url;

use CMTest_TH;
use CMTest_TestCase;
use CM\Url\BaseUrl;
use CM\Url\AppUrl;
use JsonSerializable;

class AppUrlTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        $url = AppUrl::createWithEnvironment('bar');
        $this->assertInstanceOf('CM\Url\Url', $url);
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('/bar', (string) $url);

        $url = AppUrl::createWithEnvironment('/bar?foobar=1');
        $this->assertInstanceOf('CM\Url\Url', $url);
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('/bar?foobar=1', (string) $url);

        $environment = $this->createEnvironment();
        $url = AppUrl::createWithEnvironment('/bar?foobar=1', $environment);
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('http://www.example.com/site-42/bar?foobar=1', (string) $url);

        $environment = $this->createEnvironment(['url' => 'http://www.example.com/prefix?param']);
        $url = AppUrl::createWithEnvironment('/bar?foobar=1', $environment);
        $this->assertSame('prefix', $url->getPrefix());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('http://www.example.com/prefix/site-43/bar?foobar=1', (string) $url);

        $environment = $this->createEnvironment(['url' => 'http://www.example.com/prefix?param'], null, 'de');
        $url = AppUrl::createWithEnvironment('/bar?foobar=1', $environment);
        $this->assertSame('prefix', $url->getPrefix());
        $this->assertSame($environment->getLanguage(), $url->getLanguage());
        $this->assertSame('http://www.example.com/prefix/language-de/site-44/bar?foobar=1', (string) $url);
    }
}
