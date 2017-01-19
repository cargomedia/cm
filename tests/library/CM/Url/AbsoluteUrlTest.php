<?php

namespace CM\Test\Url;

use CM_Frontend_Environment;
use CMTest_TestCase;
use CM\Url\AbsoluteUrl;

class AbsoluteUrlTest extends CMTest_TestCase {

    public function testIsValid() {
        /** @var InvalidArgumentException $exception */
        $exception = $this->catchException(function () {
            AbsoluteUrl::createFromString();
        });
        $this->assertInstanceOf('InvalidArgumentException', $exception);
        $this->assertSame('The URI components will produce a `CM\Url\AbsoluteUrl` instance in invalid state', $exception->getMessage());

        /** @var InvalidArgumentException $exception */
        $exception = $this->catchException(function () {
            AbsoluteUrl::createFromString('/foo');
        });
        $this->assertInstanceOf('InvalidArgumentException', $exception);
        $this->assertSame('The URI components will produce a `CM\Url\AbsoluteUrl` instance in invalid state', $exception->getMessage());

        /** @var InvalidArgumentException $exception */
        $exception = $this->catchException(function () {
            AbsoluteUrl::createFromString('foo://bar');
        });
        $this->assertInstanceOf('InvalidArgumentException', $exception);
        $this->assertSame('The submitted scheme is unsupported by `CM\Url\AbsoluteUrl`', $exception->getMessage());

        $url = AbsoluteUrl::createFromString('http://foo');
        $this->assertSame('http', $url->getScheme());
        $this->assertSame('foo', $url->getHost());

        $url = AbsoluteUrl::createFromString('https://foo/bar?foobar=1&barfoo#foo');
        $this->assertSame('https', $url->getScheme());
        $this->assertSame('foo', $url->getHost());
        $this->assertSame('/bar', $url->getPath());
        $this->assertSame('foo', $url->getFragment());
        $this->assertSame('barfoo&foobar=1', $url->getQuery());
    }

    public function testGetRelativeUrl() {
        $url = AbsoluteUrl::createFromString('http://foo/bar?foobar=1#foo');
        $relativeUrl = $url->getRelativeUrl();
        $this->assertInstanceOf('CM\Url\RelativeUrl', $relativeUrl);
        $this->assertSame('', $relativeUrl->getScheme());
        $this->assertSame('', $relativeUrl->getHost());
        $this->assertSame('/bar', $relativeUrl->getPath());
        $this->assertSame('foo', $relativeUrl->getFragment());
        $this->assertSame('foobar=1', $relativeUrl->getQuery());
        $this->assertSame('/bar?foobar=1#foo', (string) $relativeUrl);
    }

    public function testWithEnvironment() {
        $url = AbsoluteUrl::createFromString('http://foo/bar?foobar=1');
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
