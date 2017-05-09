<?php

namespace CM\Test\Url;

use CMTest_TH;
use CMTest_TestCase;
use CM\Url\BaseUrl;
use CM\Url\Url;
use JsonSerializable;

class UrlTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        $url = new Url('/bar?foobar=1');
        $this->assertInstanceOf('CM\Url\Url', $url);
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame('/bar?foobar=1', (string) $url);

        $url = new Url('');
        $this->assertSame('', $url->getPath());
        $this->assertSame('', (string) $url);

        $url = new Url('/');
        $this->assertSame('/', $url->getPath());
        $this->assertSame('/', (string) $url);

        $url = new Url('?foo=bar');
        $this->assertSame(['foo' => 'bar'], $url->getParams());
        $this->assertSame('/', $url->getPath());
        $this->assertSame('/?foo=bar', (string) $url);

        $url = new Url('//example.com');
        $this->assertInstanceOf('CM\Url\Url', $url);
        $this->assertSame('//example.com/', (string) $url);

        $url = new Url('http://example.com/0');
        $this->assertInstanceOf('CM\Url\Url', $url);
        $this->assertSame('http://example.com/0', (string) $url);

        $url = new Url('http://스타벅스코리아.com/path/../foo/./bar');
        $this->assertInstanceOf('CM\Url\Url', $url);
        $this->assertSame('http://스타벅스코리아.com/foo/bar', (string) $url);
    }

    public function testTrailingSlash() {
        $url = new Url('/path?bar=1');
        $this->assertFalse($url->hasTrailingSlash());
        $this->assertSame('/path', $url->getPath());
        $this->assertSame('/path?bar=1', (string) $url);
        /** @var Url $url */
        $url = $url->withPath('/foo/');
        $this->assertTrue($url->hasTrailingSlash());
        $this->assertSame('/foo/', $url->getPath());
        $this->assertSame('/foo/?bar=1', (string) $url);
        $url = $url->withoutTrailingSlash();
        $this->assertFalse($url->hasTrailingSlash());
        $this->assertSame('/foo', $url->getPath());
        $this->assertSame('/foo?bar=1', (string) $url);
        $url = $url->withTrailingSlash();
        $this->assertTrue($url->hasTrailingSlash());
        $this->assertSame('/foo/', $url->getPath());
        $this->assertSame('/foo/?bar=1', (string) $url);
    }

    public function testAppendPath() {
        $url = new Url('foo');
        $this->assertSame('/foo', $url->getPath());
        $this->assertSame('/foo/bar', $url->appendPath('bar')->getPath());
        $this->assertSame('/foo/bar', $url->appendPath('/.//bar')->getPath());
        $this->assertSame('/bar/', $url->appendPath('/..//bar/')->getPath());
    }

    public function testPrependPath() {
        $url = new Url('foo');
        $this->assertSame('/foo', $url->getPath());
        $this->assertSame('/bar/foo', $url->prependPath('bar')->getPath());
        $this->assertSame('/bar/foo', $url->prependPath('/.//bar/.')->getPath());
        $this->assertSame('/foo', $url->prependPath('/.//bar//../')->getPath());
    }

    public function testWithPrefix() {
        $url = new Url('/path?foo=1#bar');
        $this->assertSame(null, $url->getPrefix());

        $urlWithPrefix = $url->withPrefix('prefix');
        $this->assertSame('prefix', $urlWithPrefix->getPrefix());
        $this->assertSame('/prefix/path?foo=1#bar', (string) $urlWithPrefix);

        $urlWithPrefix = $url->withPrefix('/prefix/');
        $this->assertSame('prefix', $urlWithPrefix->getPrefix());
        $this->assertSame('/prefix/path?foo=1#bar', (string) $urlWithPrefix);

        $urlWithPrefix = $url->withPrefix('prefix/foo');
        $this->assertSame('prefix/foo', $urlWithPrefix->getPrefix());
        $this->assertSame('/prefix/foo/path?foo=1#bar', (string) $urlWithPrefix);

        $urlWithPrefix = $url->withPrefix('/');
        $this->assertSame(null, $urlWithPrefix->getPrefix());
        $this->assertSame('/path?foo=1#bar', (string) $urlWithPrefix);

        $urlWithPrefix = $url->withPrefix('');
        $this->assertSame(null, $urlWithPrefix->getPrefix());
        $this->assertSame('/path?foo=1#bar', (string) $urlWithPrefix);

        $urlWithPrefix = $url->withPrefix(null);
        $this->assertSame(null, $urlWithPrefix->getPrefix());
        $this->assertSame('/path?foo=1#bar', (string) $urlWithPrefix);
    }

    public function testWithParams() {
        $url = new Url('/foo');
        $object = new CM_UrlAbstractMockSerializable();

        $this->assertSame('/foo', (string) $url);
        $this->assertSame(null, $url->getParams());

        $url = new Url('bar?foo=42');
        $this->assertSame('foo=42', $url->getQuery());
        $this->assertSame(['foo' => '42'], $url->getParams());

        $url = new Url('/foo');
        $urlWithParams = $url->withParams([]);
        $this->assertSame('/foo', (string) $urlWithParams);
        $this->assertSame(null, $urlWithParams->getParams());

        $urlWithParams = $url->withParams(['foo', 'foz' => null, 'bar' => 'baz', 'val' => 1, 'obj' => $object]);
        $this->assertSame('/foo?0=foo&bar=baz&val=1&obj[_class]=CM\Test\Url\CM_UrlAbstractMockSerializable&obj[foo]=bar', urldecode((string) $urlWithParams));
        $this->assertEquals(['foo', 'foz' => null, 'bar' => 'baz', 'val' => 1, 'obj' => $object], $urlWithParams->getParams());

        $urlWithParams = $url->withParams(['foo' => ['bar' => ['val' => 1], 'baz' => ['a', 'b', 'c']]]);
        $this->assertSame('/foo?foo[bar][val]=1&foo[baz][0]=a&foo[baz][1]=b&foo[baz][2]=c', urldecode((string) $urlWithParams));
        $this->assertSame(['foo' => ['bar' => ['val' => 1], 'baz' => ['a', 'b', 'c']]], $urlWithParams->getParams());

        $urlWithQuery = $url->withQuery('');
        $this->assertSame('/foo', (string) $urlWithQuery);
        $this->assertSame(null, $urlWithQuery->getParams());

        $urlWithQuery = $url->withQuery('0=foo&123&bar=baz&val=1');
        $this->assertSame('/foo?0=foo&123&bar=baz&val=1', (string) $urlWithQuery);
        $this->assertSame(['foo', 123 => '', 'bar' => 'baz', 'val' => '1'], $urlWithQuery->getParams());

        $urlWithQuery = $url->withQuery('%%aff%%=quux&bar=%%AFF%%&baz[]=%%aff%%&baz[]=%%aff%%');
        $this->assertSame([
            '%?f%%' => 'quux',
            'bar'   => '%?F%%',
            'baz'   => [
                '%?f%%',
                '%?f%%',
            ]
        ], $urlWithQuery->getParams());

        $urlModified = $url->withQuery('foo[bar][val]=1&foo[baz][]=a&foo[baz][]=b&foo[baz][]=c');
        $this->assertSame('/foo?foo[bar][val]=1&foo[baz][]=a&foo[baz][]=b&foo[baz][]=c', urldecode((string) $urlModified));
        $this->assertSame(['foo' => ['bar' => ['val' => '1'], 'baz' => ['a', 'b', 'c']]], $urlModified->getParams());

        $urlModified = $url->withParams(['foo' => 1])->withQuery('bar=1');
        $this->assertSame('/foo?bar=1', (string) $urlModified);
        $this->assertSame(['bar' => '1'], $urlModified->getParams());
    }

    public function testWithBaseUrl() {
        $baseUrl = new BaseUrl('http://foo/?param');
        $url = new Url('/bar?foobar=1');
        $urlWithBaseUrl = $url->withBaseUrl($baseUrl);

        $this->assertSame(null, $url->getPrefix());
        $this->assertSame('/bar?foobar=1', (string) $url);
        $this->assertSame(null, $urlWithBaseUrl->getPrefix());
        $this->assertSame('http://foo/bar?foobar=1', (string) $urlWithBaseUrl);

        $baseUrlWithPrefix = $baseUrl->withPrefix('prefix');
        $urlWithBaseUrlAndPrefix = $url->withBaseUrl($baseUrlWithPrefix);

        $this->assertSame('prefix', $baseUrlWithPrefix->getPrefix());
        $this->assertSame('http://foo/prefix/', (string) $baseUrlWithPrefix);
        $this->assertSame('prefix', $urlWithBaseUrlAndPrefix->getPrefix());
        $this->assertSame('http://foo/prefix/bar?foobar=1', (string) $urlWithBaseUrlAndPrefix);

        $urlWithPrefixPreserved = $urlWithBaseUrlAndPrefix->withPath('/baz');
        $this->assertSame('prefix', $urlWithPrefixPreserved->getPrefix());
        $this->assertSame('http://foo/prefix/baz?foobar=1', (string) $urlWithPrefixPreserved);

        /** @var \CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () {
            $baseUrl = new BaseUrl('/path?param');
            new Url('/bar?foobar=1', $baseUrl);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('BaseUrl::create argument must be an absolute Url', $exception->getMessage());
        $this->assertSame([
            'url' => '/path?param',
        ], $exception->getMetaInfo());
    }

    public function testWithRelativeComponentsFrom() {
        $url1 = new Url('http://foo/path?foo=1');
        $url2 = new Url('http://bar/path?bar=1');

        $this->assertSame('http://foo/path?bar=1', (string) $url1->withRelativeComponentsFrom($url2));
    }

    public function testWithoutRelativeComponents() {
        $url = new Url('/path?foo=1');
        $this->assertSame('/', (string) $url->withoutRelativeComponents());

        $url = new Url('http://foo/path?foo=1');
        $this->assertSame('http://foo/', (string) $url->withoutRelativeComponents());
    }

    public function testIsAbsolute() {
        $url = new Url('/bar?foobar=1');
        $this->assertSame(true, $url->isRelative());

        $url = new Url('http://foo/bar?foobar=1');
        $this->assertSame(false, $url->isRelative());
    }
}

class CM_UrlAbstractMockSerializable implements JsonSerializable {

    public function jsonSerialize() {
        return ['foo' => 'bar'];
    }
}
