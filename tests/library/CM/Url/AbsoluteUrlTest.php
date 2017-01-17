<?php

use CM\Url\AbsoluteUrl;

class CM_Url_AbsoluteUrlTest extends CMTest_TestCase {

    public function testIsValid() {
        /** @var InvalidArgumentException $exception */
        $exception = $this->catchException(function () {
            $url = AbsoluteUrl::createFromString();
        });
        $this->assertInstanceOf('InvalidArgumentException', $exception);
        $this->assertSame('The URI components will produce a `CM\Url\AbsoluteUrl` instance in invalid state', $exception->getMessage());

        /** @var InvalidArgumentException $exception */
        $exception = $this->catchException(function () {
            $url = AbsoluteUrl::createFromString('/foo');
        });
        $this->assertInstanceOf('InvalidArgumentException', $exception);
        $this->assertSame('The URI components will produce a `CM\Url\AbsoluteUrl` instance in invalid state', $exception->getMessage());

        /** @var InvalidArgumentException $exception */
        $exception = $this->catchException(function () {
            $url = AbsoluteUrl::createFromString('foo://bar');
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
        $this->assertSame('foobar=1&barfoo', $url->getQuery());
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

        $siteCdn = $this->getMockSite(null, null, [
            'url'    => 'http://www.foo.com',
            'urlCdn' => 'http://cdn.foo.com',
        ]);
        $envCdn = new CM_Frontend_Environment($siteCdn);
        $envCdnUrl = $url->withEnvironment($envCdn);
        $this->assertSame('cdn.foo.com', $envCdnUrl->getHost());

        $envCdnSameOriginUrl = $url->withEnvironment($envCdn, ['sameOrigin' => true,]);
        $this->assertSame('www.foo.com', $envCdnSameOriginUrl->getHost());
    }
}
