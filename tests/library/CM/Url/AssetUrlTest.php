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
        $url = CM_Url_AssetMockUrl::create('/bar?foobar=1');

        $environment = $this->createEnvironment();
        $urlWithEnvironment = $url->withEnvironment($environment);
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('/bar?foobar=1', (string) $url);
        $this->assertSame(null, $urlWithEnvironment->getLanguage());
        $this->assertSame('http://cdn.example.com/bar?foobar=1', (string) $urlWithEnvironment);

        $environment = $this->createEnvironment(null, null, 'de');
        $urlWithEnvironmentAndLanguage = $url->withEnvironment($environment);
        $this->assertSame($environment->getLanguage(), $urlWithEnvironmentAndLanguage->getLanguage());
        $this->assertSame('http://cdn.example.com/de/bar?foobar=1', (string) $urlWithEnvironmentAndLanguage);

        $urlWithEnvironmentPreserved = $urlWithEnvironmentAndLanguage->withPath('/baz');
        $this->assertSame($environment->getLanguage(), $urlWithEnvironmentPreserved->getLanguage());
        $this->assertSame('http://cdn.example.com/de/baz?foobar=1', (string) $urlWithEnvironmentPreserved);
    }

    public function testWithSite() {
        $site = $this->getMockSite();

        $url = CM_Url_AssetMockUrl::create('/bar?foobar=1');
        $urlWithSite = $url->withSite($site);
        $this->assertSame('http://cdn.example.com/bar?foobar=1', (string) $urlWithSite);
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
