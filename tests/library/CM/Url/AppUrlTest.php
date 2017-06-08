<?php

namespace CM\Test\Url;

use CMTest_TH;
use CMTest_TestCase;
use CM\Url\AppUrl;

class AppUrlTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        $url = AppUrl::createWithEnvironment('bar');
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('/bar', (string) $url);

        $url = AppUrl::createWithEnvironment('/bar?foobar=1');
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('/bar?foobar=1', (string) $url);

        $environment = $this->createEnvironment(['type' => 900]);
        $url = AppUrl::createWithEnvironment('/bar?foobar=1', $environment);
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('http://www.example.com/site-900/bar?foobar=1', (string) $url);

        $environment = $this->createEnvironment(['type' => 901, 'url' => 'http://www.example.com/prefix?param']);
        $url = AppUrl::createWithEnvironment('/bar?foobar=1', $environment);
        $this->assertSame('prefix', $url->getPrefix());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('http://www.example.com/prefix/site-901/bar?foobar=1', (string) $url);

        $environment = $this->createEnvironment(['type' => 902, 'url' => 'http://www.example.com/prefix?param'], null, 'de');
        $url = AppUrl::createWithEnvironment('/bar?foobar=1', $environment);
        $this->assertSame('prefix', $url->getPrefix());
        $this->assertSame($environment->getLanguage(), $url->getLanguage());
        $this->assertSame('http://www.example.com/prefix/language-de/site-902/bar?foobar=1', (string) $url);
    }

    public function testCreateFromString() {
        $environment = $this->createEnvironment(['type' => 900], null, 'de');
        $site = $environment->getSite();
        $language = $environment->getLanguage();

        $url = AppUrl::createFromString('bar');
        $this->assertSame(null, $url->getSite());
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('/bar', (string) $url);

        $url = AppUrl::createFromString('/language-de/bar');
        $this->assertSame(null, $url->getSite());
        $this->assertEquals($language, $url->getLanguage());
        $this->assertSame('/bar', $url->getPath());
        $this->assertSame('/language-de/bar', (string) $url);

        $url = AppUrl::createFromString('http://foo.com/language-de/site-900/bar');
        $this->assertEquals($site, $url->getSite());
        $this->assertEquals($language, $url->getLanguage());
        $this->assertSame('/bar', $url->getPath());
        $this->assertSame('http://www.example.com/language-de/site-900/bar', (string) $url);

        $url = AppUrl::createFromString($site->getUrlString() . '/language-de/bar');
        $this->assertEquals($site, $url->getSite());
        $this->assertSame('de', $url->getLanguage()->getAbbreviation());
        $this->assertSame('/bar', $url->getPath());
        $this->assertSame('http://www.example.com/language-de/site-900/bar', (string) $url);

        $siteFoo = $this->getMockSite(null, null, [
            'url' => 'https://foo.com/',
        ]);
        $url = AppUrl::createFromString('http://foo.com/language-de/bar');
        $this->assertEquals($siteFoo, $url->getSite());
        $this->assertEquals($language, $url->getLanguage());
        $this->assertSame('/bar', $url->getPath());
        $this->assertSame('https://foo.com/language-de/site-' . $siteFoo->getId() . '/bar', (string) $url);

        $url = AppUrl::createFromString('http://foo.com/language-de/site-900/bar');
        $this->assertEquals($site, $url->getSite());
        $this->assertEquals($language, $url->getLanguage());
        $this->assertSame('/bar', $url->getPath());
        $this->assertSame('http://www.example.com/language-de/site-900/bar', (string) $url);
    }

    public function testWithLanguage() {
        $language = CMTest_TH::createLanguage('de');
        $url = new AppUrl('/bar?foobar=1');
        $urlWithLanguage = $url->withLanguage($language);

        $this->assertNotEquals($url, $urlWithLanguage);
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('/bar?foobar=1', (string) $url);
        $this->assertSame('/bar', $urlWithLanguage->getPath());
        $this->assertSame($language, $urlWithLanguage->getLanguage());
        $this->assertSame('/language-de/bar?foobar=1', (string) $urlWithLanguage);
    }

    public function testWithEnvironment() {
        $url = new AppUrl('/bar?foobar=1');

        $environment = $this->createEnvironment(['type' => 900]);
        $urlWithEnvironment = $url->withEnvironment($environment);
        $this->assertSame(null, $url->getLanguage());
        $this->assertSame('/bar?foobar=1', (string) $url);
        $this->assertSame($environment->getSite(), $urlWithEnvironment->getSite());
        $this->assertSame(null, $urlWithEnvironment->getLanguage());
        $this->assertSame('/bar', $urlWithEnvironment->getPath());
        $this->assertSame('http://www.example.com/site-900/bar?foobar=1', (string) $urlWithEnvironment);

        $environment = $this->createEnvironment(['type' => 901], null, 'de');
        $urlWithEnvironmentAndLanguage = $url->withEnvironment($environment);
        $this->assertSame($environment->getSite(), $urlWithEnvironmentAndLanguage->getSite());
        $this->assertSame($environment->getLanguage(), $urlWithEnvironmentAndLanguage->getLanguage());
        $this->assertSame('/bar', $urlWithEnvironmentAndLanguage->getPath());
        $this->assertSame('http://www.example.com/language-de/site-901/bar?foobar=1', (string) $urlWithEnvironmentAndLanguage);

        $urlWithEnvironmentPreserved = $urlWithEnvironmentAndLanguage->withPath('/baz');
        $this->assertSame($environment->getSite(), $urlWithEnvironmentPreserved->getSite());
        $this->assertSame($environment->getLanguage(), $urlWithEnvironmentPreserved->getLanguage());
        $this->assertSame('/baz', $urlWithEnvironmentPreserved->getPath());
        $this->assertSame('http://www.example.com/language-de/site-901/baz?foobar=1', (string) $urlWithEnvironmentPreserved);
    }
}
