<?php

namespace CM\Test\Url;

use CM_App;
use CM_Frontend_Environment;
use CMTest_TH;
use CMTest_TestCase;
use CM\Url\ResourceUrl;

class ResourceUrlTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testInstantiation() {
        $url = ResourceUrl::createFromString('bar?foobar=1#foo');
        $this->assertSame('bar?foobar=1#foo', (string) $url);
        $this->assertSame('', $url->getType());

        $url = ResourceUrl::createFromString('bar', 'resource-type');
        $this->assertSame('bar', (string) $url);
        $this->assertSame('resource-type', $url->getType());
    }

    public function testWithEnvironment() {
        $site = $this->getMockSite(null, 42, [
            'url' => 'http://www.foo.com',
        ]);
        $url = ResourceUrl::createFromString('bar?foobar=1#foo', 'resource-type');
        $env = new CM_Frontend_Environment($site);
        $envUrl = $url->withEnvironment($env);
        $version = CM_App::getInstance()->getDeployVersion();
        $this->assertSame(sprintf('http://www.foo.com/resource-type/42/%s/bar?foobar=1#foo', $version), (string) $envUrl);
    }

    public function testWithEnvironmentNoType() {
        $site = $this->getMockSite(null, 42, [
            'url' => 'http://www.foo.com',
        ]);

        $url = ResourceUrl::createFromString('bar?foobar=1#foo');
        $env = new CM_Frontend_Environment($site);
        $envUrl = $url->withEnvironment($env);
        $version = CM_App::getInstance()->getDeployVersion();
        $this->assertSame(sprintf('http://www.foo.com/42/%s/bar?foobar=1#foo', $version), (string) $envUrl);
    }

    public function testWithEnvironmentWithLanguage() {
        $site = $this->getMockSite(null, 42, [
            'url' => 'http://www.foo.com',
        ]);
        $url = ResourceUrl::createFromString('bar?foobar=1#foo', 'resource-type');
        $lang = CMTest_TH::createLanguage('de');
        $env = new CM_Frontend_Environment($site, null, $lang);
        $envUrl = $url->withEnvironment($env);
        $version = CM_App::getInstance()->getDeployVersion();
        $this->assertSame(sprintf('http://www.foo.com/resource-type/de/42/%s/bar?foobar=1#foo', $version), (string) $envUrl);
    }
}
