<?php

namespace CM\Test\Url;

use CM_Frontend_Environment;
use CMTest_TestCase;
use CM\Url\RelativeUrl;
use CM\Url\AbsoluteUrl;

class RelativeUrlTest extends CMTest_TestCase {

    public function testIsValid() {
        /** @var InvalidArgumentException $exception */
        $exception = $this->catchException(function () {
            RelativeUrl::createFromString('foo://bar');
        });
        $this->assertInstanceOf('InvalidArgumentException', $exception);
        $this->assertSame('The URI components will produce a `CM\Url\RelativeUrl` instance in invalid state', $exception->getMessage());

        /** @var InvalidArgumentException $exception */
        $exception = $this->catchException(function () {
            RelativeUrl::createFromString('foo:/');
        });
        $this->assertInstanceOf('InvalidArgumentException', $exception);
        $this->assertSame('The URI components will produce a `CM\Url\RelativeUrl` instance in invalid state', $exception->getMessage());

        $url = RelativeUrl::createFromString('foo');
        $this->assertSame('', $url->getScheme());
        $this->assertSame('', $url->getHost());
        $this->assertSame('foo', $url->getPath());
        $this->assertSame('', $url->getFragment());
        $this->assertSame('', $url->getQuery());

        $url = RelativeUrl::createFromString('/bar?foobar=1&barfoo#foo');
        $this->assertSame('', $url->getScheme());
        $this->assertSame('', $url->getHost());
        $this->assertSame('/bar', $url->getPath());
        $this->assertSame('foo', $url->getFragment());
        $this->assertSame('barfoo&foobar=1', $url->getQuery());
    }

    public function testWithEnvironment() {
        $url = RelativeUrl::createFromString('/bar?foobar=1');
        $site = $this->getMockSite(null, null, [
            'url' => 'http://www.foo.com',
        ]);
        $env = new CM_Frontend_Environment($site);

        $envUrl = $url->withEnvironment($env);
        $this->assertSame('http', $envUrl->getScheme());
        $this->assertSame('www.foo.com', $envUrl->getHost());
        $this->assertSame('/bar', $envUrl->getPath());
        $this->assertSame('foobar=1', $envUrl->getQuery());
        $this->assertSame('http://www.foo.com/bar?foobar=1', (string) $envUrl);

        $siteCdn = $this->getMockSite(null, null, [
            'url'    => 'http://www.foo.com',
            'urlCdn' => 'http://cdn.foo.com',
        ]);
        $envCdn = new CM_Frontend_Environment($siteCdn);
        $envCdnUrl = $url->withEnvironment($envCdn);
        $this->assertSame('cdn.foo.com', $envCdnUrl->getHost());
        $this->assertSame('http://cdn.foo.com/bar?foobar=1', (string) $envCdnUrl);

        $envCdnSameOriginUrl = $url->withEnvironment($envCdn, ['sameOrigin' => true,]);
        $this->assertSame('www.foo.com', $envCdnSameOriginUrl->getHost());
        $this->assertSame('http://www.foo.com/bar?foobar=1', (string) $envCdnSameOriginUrl);
    }
}
