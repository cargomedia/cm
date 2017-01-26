<?php

namespace CM\Test\Url;

use CMTest_TH;
use CMTest_TestCase;
use CM_Model_Language;
use CM_Frontend_Environment;
use CM\Url\UrlInterface;
use CM\Url\AssetUrl;
use League\Uri\Components\HierarchicalPath;

class AssetUrlTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testWithEnvironment() {
        $site = $this
            ->getMockBuilder('CM_Site_Abstract')
            ->setMethods(['getUrl', 'getUrlCdn'])
            ->getMockForAbstractClass();

        $site
            ->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue('http://foo/path?param'));

        $site
            ->expects($this->any())
            ->method('getUrlCdn')
            ->will($this->returnValue('http://cdn.foo/path?param'));

        $url = CM_Url_AssetMockUrl::create('/bar?foobar=1');

        $environment = new CM_Frontend_Environment($site, null, null);
        $urlWithEnvironment = $url->withEnvironment($environment);
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('/bar?foobar=1', (string) $url);
        $this->assertSame(null, $urlWithEnvironment->getLanguage());
        $this->assertSame('http://cdn.foo/bar?foobar=1', (string) $urlWithEnvironment);

        $language = CMTest_TH::createLanguage('de');
        $environment = new CM_Frontend_Environment($site, null, $language);
        $urlWithEnvironmentAndLanguage = $url->withEnvironment($environment);
        $this->assertSame($language, $urlWithEnvironmentAndLanguage->getLanguage());
        $this->assertSame('http://cdn.foo/de/bar?foobar=1', (string) $urlWithEnvironmentAndLanguage);

        $urlWithEnvironmentPreserved = $urlWithEnvironmentAndLanguage->withPath('/baz');
        $this->assertSame($language, $urlWithEnvironmentPreserved->getLanguage());
        $this->assertSame('http://cdn.foo/de/baz?foobar=1', (string) $urlWithEnvironmentPreserved);

        $urlWithEnvironmentSameOrigin = $url->withEnvironment($environment, ['sameOrigin' => true]);
        $this->assertSame('http://foo/de/bar?foobar=1', (string) $urlWithEnvironmentSameOrigin);
    }

    public function testWithSite() {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\CM_Site_Abstract $site */
        $site = $this
            ->getMockBuilder('CM_Site_Abstract')
            ->setMethods(['getUrl', 'getUrlCdn'])
            ->getMockForAbstractClass();

        $site
            ->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue('http://foo/path?param'));

        $site
            ->expects($this->any())
            ->method('getUrlCdn')
            ->will($this->returnValue('http://cdn.foo/path?param'));

        $url = CM_Url_AssetMockUrl::create('/bar?foobar=1');

        $urlWithSite = $url->withSite($site);
        $this->assertSame('http://cdn.foo/bar?foobar=1', (string) $urlWithSite);

        $urlWithSiteSameOrigin = $url->withSite($site, true);
        $this->assertSame('http://foo/bar?foobar=1', (string) $urlWithSiteSameOrigin);
    }
}

class CM_Url_AssetMockUrl extends AssetUrl {

    public function getUriRelativeComponents() {
        $segments = [];
        if ($language = $this->getLanguage()) {
            $segments[] = $language->getAbbreviation();
        }
        if ($deployVersion = $this->getDeployVersion()) {
            $segments[] = $deployVersion;
        }
        $path = $this->path->prepend(
            HierarchicalPath::createFromSegments($segments, HierarchicalPath::IS_ABSOLUTE)
        );
        return ''
            . $path->getUriComponent()
            . $this->query->getUriComponent()
            . $this->fragment->getUriComponent();
    }

    public static function create($url, UrlInterface $baseUrl = null, CM_Model_Language $language = null) {
        return parent::_create($url, $baseUrl, $language);
    }
}
