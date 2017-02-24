<?php

namespace CM\Test\Url;

use CMTest_TH;
use CMTest_TestCase;
use CM_Frontend_Environment;
use CM\Url\BaseUrl;
use CM\Url\AbstractUrl;
use JsonSerializable;

class AbstractUrlTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreateFromString() {
        $url = new CM_Url_AbstractMockUrl('/bar?foobar=1');
        $this->assertInstanceOf('CM\Url\AbstractUrl', $url);
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('/bar?foobar=1', (string) $url);

        $url = new CM_Url_AbstractMockUrl('http://스타벅스코리아.com/path/../foo/./bar');
        $this->assertInstanceOf('CM\Url\AbstractUrl', $url);
        $this->assertSame('http://스타벅스코리아.com/foo/bar', (string) $url);
    }

    public function testCreate() {
        $url = CM_Url_AbstractMockUrl::create('bar');
        $this->assertInstanceOf('CM\Url\AbstractUrl', $url);
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('/bar', (string) $url);

        $url = CM_Url_AbstractMockUrl::create('/bar?foobar=1');
        $this->assertInstanceOf('CM\Url\AbstractUrl', $url);
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('/bar?foobar=1', (string) $url);

        $environment = $this->createEnvironment();
        $url = CM_Url_AbstractMockUrl::create('/bar?foobar=1', $environment);
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('http://www.example.com/bar?foobar=1', (string) $url);

        $environment = $this->createEnvironment(['url' => 'http://www.example.com/prefix?param']);
        $url = CM_Url_AbstractMockUrl::create('/bar?foobar=1', $environment);
        $this->assertSame('prefix', $url->getPrefix());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('http://www.example.com/prefix/bar?foobar=1', (string) $url);

        $environment = $this->createEnvironment(['url' => 'http://www.example.com/prefix?param'], null, 'de');
        $url = CM_Url_AbstractMockUrl::create('/bar?foobar=1', $environment);
        $this->assertSame('prefix', $url->getPrefix());
        $this->assertSame($environment->getLanguage(), $url->getLanguage());
        $this->assertSame('http://www.example.com/prefix/bar/de?foobar=1', (string) $url);
    }

    public function testWithPrefix() {
        $url = new CM_Url_AbstractMockUrl('/path?foo=1#bar');
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
        $url = new CM_Url_AbstractMockUrl('/foo');
        $object = new CM_UrlAbstractMockSerializable();

        $this->assertSame('/foo', (string) $url);
        $this->assertSame(null, $url->getParams());

        $urlWithParams = $url->withParams(['foo', 'foz' => null, 'bar' => 'baz', 'val' => 1, 'obj' => $object]);
        $this->assertSame('/foo?0=foo&bar=baz&val=1&obj[_class]=CM\Test\Url\CM_UrlAbstractMockSerializable&obj[foo]=bar', urldecode((string) $urlWithParams));
        $this->assertEquals(['foo', 'foz' => null, 'bar' => 'baz', 'val' => 1, 'obj' => $object], $urlWithParams->getParams());

        $urlWithParams = $url->withParams(['foo' => ['bar' => ['val' => 1], 'baz' => ['a', 'b', 'c']]]);
        $this->assertSame('/foo?foo[bar][val]=1&foo[baz][0]=a&foo[baz][1]=b&foo[baz][2]=c', urldecode((string) $urlWithParams));
        $this->assertSame(['foo' => ['bar' => ['val' => 1], 'baz' => ['a', 'b', 'c']]], $urlWithParams->getParams());

        $urlWithQuery = $url->withQuery('');
        $this->assertSame('/foo', (string) $urlWithQuery);
        $this->assertSame([], $urlWithQuery->getParams());

        $urlWithQuery = $url->withQuery('0=foo&123&bar=baz&val=1');
        $this->assertSame('/foo?0=foo&123&bar=baz&val=1', (string) $urlWithQuery);
        $this->assertSame(['foo', 123 => '', 'bar' => 'baz', 'val' => '1'], $urlWithQuery->getParams());

        $urlModified = $url->withQuery('foo[bar][val]=1&foo[baz][]=a&foo[baz][]=b&foo[baz][]=c');
        $this->assertSame('/foo?foo[bar][val]=1&foo[baz][]=a&foo[baz][]=b&foo[baz][]=c', urldecode((string) $urlModified));
        $this->assertSame(['foo' => ['bar' => ['val' => '1'], 'baz' => ['a', 'b', 'c']]], $urlModified->getParams());

        $urlModified = $url->withParams(['foo' => 1])->withQuery('bar=1');
        $this->assertSame('/foo?bar=1', (string) $urlModified);
        $this->assertSame(['bar' => '1'], $urlModified->getParams());
    }

    public function testWithLanguage() {
        $language = CMTest_TH::createLanguage('de');
        $url = new CM_Url_AbstractMockUrl('/bar?foobar=1');
        $urlWithLanguage = $url->withLanguage($language);

        $this->assertNotEquals($url, $urlWithLanguage);
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('/bar?foobar=1', (string) $url);
        $this->assertSame($language, $urlWithLanguage->getLanguage());
        $this->assertSame('/bar/de?foobar=1', (string) $urlWithLanguage);
    }

    public function testWithBaseUrl() {
        $baseUrl = new BaseUrl('http://foo/?param');
        $url = new CM_Url_AbstractMockUrl('/bar?foobar=1');
        $urlWithBaseUrl = $url->withBaseUrl($baseUrl);

        $this->assertSame(null, $url->getPrefix());
        $this->assertSame('/bar?foobar=1', (string) $url);
        $this->assertSame(null, $urlWithBaseUrl->getPrefix());
        $this->assertSame('http://foo/bar?foobar=1', (string) $urlWithBaseUrl);

        $baseUrlWithPrefix = $baseUrl->withPrefix('prefix');
        $urlWithBaseUrlAndPrefix = $url->withBaseUrl($baseUrlWithPrefix);

        $this->assertSame('prefix', $baseUrlWithPrefix->getPrefix());
        $this->assertSame('http://foo/prefix', (string) $baseUrlWithPrefix);
        $this->assertSame('prefix', $urlWithBaseUrlAndPrefix->getPrefix());
        $this->assertSame('http://foo/prefix/bar?foobar=1', (string) $urlWithBaseUrlAndPrefix);

        $urlWithPrefixPreserved = $urlWithBaseUrlAndPrefix->withPath('/baz');
        $this->assertSame('prefix', $urlWithPrefixPreserved->getPrefix());
        $this->assertSame('http://foo/prefix/baz?foobar=1', (string) $urlWithPrefixPreserved);

        /** @var \CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () {
            $baseUrl = new BaseUrl('/path?param');
            new CM_Url_AbstractMockUrl('/bar?foobar=1', $baseUrl);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('BaseUrl::create argument must be an absolute Url', $exception->getMessage());
        $this->assertSame([
            'url' => '/path?param',
        ], $exception->getMetaInfo());
    }

    public function testWithEnvironment() {
        $url = new CM_Url_AbstractMockUrl('/bar?foobar=1');

        $environment = $this->createEnvironment();
        $urlWithEnvironment = $url->withEnvironment($environment);
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('/bar?foobar=1', (string) $url);
        $this->assertSame($environment->getSite(), $urlWithEnvironment->getSite());
        $this->assertSame(null, $urlWithEnvironment->getLanguage());
        $this->assertSame('http://www.example.com/bar?foobar=1', (string) $urlWithEnvironment);

        $environment = $this->createEnvironment(null, null, 'de');
        $urlWithEnvironmentAndLanguage = $url->withEnvironment($environment);
        $this->assertSame($environment->getSite(), $urlWithEnvironmentAndLanguage->getSite());
        $this->assertSame($environment->getLanguage(), $urlWithEnvironmentAndLanguage->getLanguage());
        $this->assertSame('http://www.example.com/bar/de?foobar=1', (string) $urlWithEnvironmentAndLanguage);

        $urlWithEnvironmentPreserved = $urlWithEnvironmentAndLanguage->withPath('/baz');
        $this->assertSame($environment->getSite(), $urlWithEnvironmentPreserved->getSite());
        $this->assertSame($environment->getLanguage(), $urlWithEnvironmentPreserved->getLanguage());
        $this->assertSame('http://www.example.com/baz/de?foobar=1', (string) $urlWithEnvironmentPreserved);
    }

    public function testWithRelativeComponentsFrom() {
        $url1 = new CM_Url_AbstractMockUrl('http://foo/path?foo=1');
        $url2 = new CM_Url_AbstractMockUrl('http://bar/path?bar=1');

        $this->assertSame('http://foo/path?bar=1', (string) $url1->withRelativeComponentsFrom($url2));
    }

    public function testWithoutRelativeComponents() {
        $url = new CM_Url_AbstractMockUrl('/path?foo=1');
        $this->assertSame('/', (string) $url->withoutRelativeComponents());

        $url = new CM_Url_AbstractMockUrl('http://foo/path?foo=1');
        $this->assertSame('http://foo/', (string) $url->withoutRelativeComponents());
    }

    public function testIsAbsolute() {
        $url = new CM_Url_AbstractMockUrl('/bar?foobar=1');
        $this->assertSame(false, $url->isAbsolute());

        $url = new CM_Url_AbstractMockUrl('http://foo/bar?foobar=1');
        $this->assertSame(true, $url->isAbsolute());
    }
}

class CM_Url_AbstractMockUrl extends AbstractUrl {

    public function getUriRelativeComponents() {
        $segments = $this->getPathSegments();
        if ($prefix = $this->getPrefix()) {
            $segments = array_merge([$prefix], $segments);
        }
        if ($language = $this->getLanguage()) {
            $segments[] = $language->getAbbreviation();
        }
        return '/' . implode('/', $segments) . $this->getQueryComponent() . $this->getFragmentComponent();
    }

    public static function create($url, CM_Frontend_Environment $environment = null) {
        return parent::_create($url, $environment);
    }
}

class CM_UrlAbstractMockSerializable implements JsonSerializable {

    public function jsonSerialize() {
        return ['foo' => 'bar'];
    }
}
