<?php

namespace CM\Test\Url;

use CMTest_TH;
use CMTest_TestCase;
use CM_Model_Language;
use CM_Frontend_Environment;
use CM\Url\UrlInterface;
use CM\Url\BaseUrl;
use CM\Url\AbstractUrl;
use League\Uri\Components\HierarchicalPath;

class AbstractUrlTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreateFromString() {
        $url = CM_Url_AbstractMockUrl::createFromString('/bar?foobar=1');
        $this->assertInstanceOf('CM\Url\AbstractUrl', $url);
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('/bar?foobar=1', (string) $url);

        $url = CM_Url_AbstractMockUrl::createFromString('http://스타벅스코리아.com/path/../foo/./bar');
        $this->assertInstanceOf('CM\Url\AbstractUrl', $url);
        $this->assertSame('http://스타벅스코리아.com/path/../foo/./bar', (string) $url);
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

        $url = CM_Url_AbstractMockUrl::create('http://스타벅스코리아.com/path/../foo/./bar');
        $this->assertInstanceOf('CM\Url\AbstractUrl', $url);
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('http://xn--oy2b35ckwhba574atvuzkc.com/foo/bar', (string) $url);

        $baseUrl = BaseUrl::create('http://foo/?param');
        $url = CM_Url_AbstractMockUrl::create('/bar?foobar=1', $baseUrl);
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('http://foo/bar?foobar=1', (string) $url);

        $baseUrlWithPrefix = BaseUrl::create('http://foo/prefix?param');
        $url = CM_Url_AbstractMockUrl::create('/bar?foobar=1', $baseUrlWithPrefix);
        $this->assertSame('prefix', $url->getPrefix());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('http://foo/prefix/bar?foobar=1', (string) $url);

        $language = CMTest_TH::createLanguage('de');
        $url = CM_Url_AbstractMockUrl::create('/bar?foobar=1', null, $language);
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame($language, $url->getLanguage());
        $this->assertSame('/bar/de?foobar=1', (string) $url);

        $url = CM_Url_AbstractMockUrl::create('/bar?foobar=1', $baseUrlWithPrefix, $language);
        $this->assertSame('prefix', $url->getPrefix());
        $this->assertSame($language, $url->getLanguage());
        $this->assertSame('http://foo/prefix/bar/de?foobar=1', (string) $url);
    }

    public function testWithPrefix() {
        $url = CM_Url_AbstractMockUrl::create('/path?foo=1#bar');
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

        $urlWithPrefix = $url->withPrefix(new HierarchicalPath('prefix'));
        $this->assertSame('prefix', $urlWithPrefix->getPrefix());
        $this->assertSame('/prefix/path?foo=1#bar', (string) $urlWithPrefix);

        $urlWithPrefix = $url->withPrefix(new HierarchicalPath('/'));
        $this->assertSame(null, $urlWithPrefix->getPrefix());
        $this->assertSame('/path?foo=1#bar', (string) $urlWithPrefix);

        $urlWithPrefix = $url->withPrefix('/');
        $this->assertSame(null, $urlWithPrefix->getPrefix());
        $this->assertSame('/path?foo=1#bar', (string) $urlWithPrefix);

        $urlWithPrefix = $url->withPrefix(new HierarchicalPath(''));
        $this->assertSame(null, $urlWithPrefix->getPrefix());
        $this->assertSame('/path?foo=1#bar', (string) $urlWithPrefix);

        $urlWithPrefix = $url->withPrefix('');
        $this->assertSame(null, $urlWithPrefix->getPrefix());
        $this->assertSame('/path?foo=1#bar', (string) $urlWithPrefix);

        $urlWithPrefix = $url->withPrefix(null);
        $this->assertSame(null, $urlWithPrefix->getPrefix());
        $this->assertSame('/path?foo=1#bar', (string) $urlWithPrefix);
    }

    public function testWithLanguage() {
        $language = CMTest_TH::createLanguage('de');
        $url = CM_Url_AbstractMockUrl::create('/bar?foobar=1');
        $urlWithLanguage = $url->withLanguage($language);

        $this->assertNotEquals($url, $urlWithLanguage);
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('/bar?foobar=1', (string) $url);
        $this->assertSame($language, $urlWithLanguage->getLanguage());
        $this->assertSame('/bar/de?foobar=1', (string) $urlWithLanguage);
    }

    public function testWithBaseUrl() {
        $baseUrl = BaseUrl::create('http://foo/?param');
        $url = CM_Url_AbstractMockUrl::create('/bar?foobar=1');
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
            $baseUrl = BaseUrl::create('/path?param');
            CM_Url_AbstractMockUrl::create('/bar?foobar=1', $baseUrl);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('BaseUrl::create argument must be an absolute Url', $exception->getMessage());
        $this->assertSame([
            'url' => '/path?param',
        ], $exception->getMetaInfo());
    }

    public function testWithEnvironment() {
        $site = $this
            ->getMockBuilder('CM_Site_Abstract')
            ->setMethods(['getUrl'])
            ->getMockForAbstractClass();

        $site
            ->expects($this->exactly(2))
            ->method('getUrl')
            ->will($this->returnValue('http://foo/?param'));

        $url = CM_Url_AbstractMockUrl::create('/bar?foobar=1');

        $environment = new CM_Frontend_Environment($site, null, null);
        $urlWithEnvironment = $url->withEnvironment($environment);
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('/bar?foobar=1', (string) $url);
        $this->assertSame(null, $urlWithEnvironment->getLanguage());
        $this->assertSame('http://foo/bar?foobar=1', (string) $urlWithEnvironment);

        $language = CMTest_TH::createLanguage('de');
        $environment = new CM_Frontend_Environment($site, null, $language);
        $urlWithEnvironmentAndLanguage = $url->withEnvironment($environment);
        $this->assertSame($language, $urlWithEnvironmentAndLanguage->getLanguage());
        $this->assertSame('http://foo/bar/de?foobar=1', (string) $urlWithEnvironmentAndLanguage);

        $urlWithEnvironmentPreserved = $urlWithEnvironmentAndLanguage->withPath('/baz');
        $this->assertSame($language, $urlWithEnvironmentPreserved->getLanguage());
        $this->assertSame('http://foo/baz/de?foobar=1', (string) $urlWithEnvironmentPreserved);
    }

    public function testWithRelativeComponentsFrom() {
        $url1 = CM_Url_AbstractMockUrl::createFromString('http://foo/path?foo=1');
        $url2 = CM_Url_AbstractMockUrl::createFromString('http://bar/path?bar=1');

        $this->assertSame('http://foo/path?bar=1', (string) $url1->withRelativeComponentsFrom($url2));
    }

    public function testWithoutRelativeComponents() {
        $url = CM_Url_AbstractMockUrl::createFromString('/path?foo=1');
        $this->assertSame('/', (string) $url->withoutRelativeComponents());

        $url = CM_Url_AbstractMockUrl::createFromString('http://foo/path?foo=1');
        $this->assertSame('http://foo/', (string) $url->withoutRelativeComponents());
    }

    public function testIsAbsolute() {
        $url = CM_Url_AbstractMockUrl::createFromString('/bar?foobar=1');
        $this->assertSame(false, $url->isAbsolute());

        $url = CM_Url_AbstractMockUrl::createFromString('http://foo/bar?foobar=1');
        $this->assertSame(true, $url->isAbsolute());
    }
}

class CM_Url_AbstractMockUrl extends AbstractUrl {

    protected function _getUriRelativeComponents() {
        $path = $this->path;
        if ($prefix = $this->getPrefix()) {
            $path = $path->prepend($prefix);
        }
        if ($language = $this->getLanguage()) {
            $path = $path->append($language->getAbbreviation());
        }
        return ''
            . $path->getUriComponent()
            . $this->query->getUriComponent()
            . $this->fragment->getUriComponent();
    }

    public static function create($url, UrlInterface $baseUrl = null, CM_Model_Language $language = null) {
        return parent::_create($url, $baseUrl, $language);
    }
}
