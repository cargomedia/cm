<?php

namespace CM\Test\Url;

use CMTest_TestCase;
use CM\Url\AbstractUrl;

class AbstractUrlTest extends CMTest_TestCase {

    public function testCreateFromString() {
        $url = CM_Url_MockUrl::createFromString('/bar?foobar=1');
        $this->assertInstanceOf('CM\Url\AbstractUrl', $url);
        $this->assertSame(false, $url->hasPathPrefix());
        $this->assertSame('/bar?foobar=1', (string) $url);

        $url = CM_Url_MockUrl::createFromString('/bar?foobar=1', 'foo');
        $this->assertInstanceOf('CM\Url\AbstractUrl', $url);
        $this->assertSame(true, $url->hasPathPrefix());
        $this->assertSame('foo', $url->getPathPrefix());
        $this->assertSame('/foo/bar?foobar=1', (string) $url);
    }

    public function testCreateFromComponents() {
        $url = CM_Url_MockUrl::createFromComponents([
            'path'  => '/bar',
            'query' => 'foobar=1'
        ]);
        $this->assertInstanceOf('CM\Url\AbstractUrl', $url);
        $this->assertSame(false, $url->hasPathPrefix());
        $this->assertSame('/bar?foobar=1', (string) $url);

        $url = CM_Url_MockUrl::createFromComponents([
            'pathPrefix' => 'foo',
            'path'       => '/bar',
            'query'      => 'foobar=1'
        ]);
        $this->assertInstanceOf('CM\Url\AbstractUrl', $url);
        $this->assertSame(true, $url->hasPathPrefix());
        $this->assertSame('foo', $url->getPathPrefix());
        $this->assertSame('/foo/bar?foobar=1', (string) $url);
    }

    public function testGetPathPrefix() {
        $url = CM_Url_MockUrl::createFromString('/bar?foobar=1');
        $this->assertSame('', $url->getPathPrefix());
        $url = CM_Url_MockUrl::createFromString('/bar?foobar=1', 'foo/bar');
        $this->assertSame('foo/bar', $url->getPathPrefix());
    }

    public function testHasPathPrefix() {
        $url = CM_Url_MockUrl::createFromString('/bar?foobar=1');
        $this->assertSame(false, $url->hasPathPrefix());
        $url = CM_Url_MockUrl::createFromString('/bar?foobar=1', 'foo');
        $this->assertSame(true, $url->hasPathPrefix());
    }

    public function testWithPathPrefix() {
        $url = CM_Url_MockUrl::createFromString('/bar?foobar=1');
        $this->assertSame(false, $url->hasPathPrefix());

        $url = $url->withPathPrefix('foo');
        $this->assertInstanceOf('CM\Url\AbstractUrl', $url);
        $this->assertSame(true, $url->hasPathPrefix());
        $this->assertSame('foo', $url->getPathPrefix());
        $this->assertSame('/foo/bar?foobar=1', (string) $url);

        $url = $url->withPath('baz');
        $this->assertInstanceOf('CM\Url\AbstractUrl', $url);
        $this->assertSame(true, $url->hasPathPrefix());
        $this->assertSame('foo', $url->getPathPrefix());
        $this->assertSame('/foo/baz?foobar=1', (string) $url);
    }

    public function testWithoutPathPrefix() {
        $url = CM_Url_MockUrl::createFromString('/bar?foobar=1', 'foo');
        $this->assertSame(true, $url->hasPathPrefix());

        $url = $url->withoutPathPrefix();
        $this->assertInstanceOf('CM\Url\AbstractUrl', $url);
        $this->assertSame(false, $url->hasPathPrefix());
        $this->assertSame('/bar?foobar=1', (string) $url);
    }

    public function testWithRelativeComponentsFrom() {
        $url1 = CM_Url_MockUrl::createFromString('http://foo/path?foo=1');
        $url2 = CM_Url_MockUrl::createFromString('http://bar/path?bar=1');

        $this->assertSame('http://foo/path?bar=1', (string) $url1->withRelativeComponentsFrom($url2));
    }

    public function testIsRelativeUrl() {
        $url = CM_Url_MockUrl::createFromString('/bar?foobar=1');
        $this->assertSame(true, $url->isRelativeUrl());

        $url = CM_Url_MockUrl::createFromString('http://foo/bar?foobar=1');
        $this->assertSame(false, $url->isRelativeUrl());
    }
}

class CM_Url_MockUrl extends AbstractUrl {

    protected function isValid() {
        return true;
    }

    public function withEnvironment(\CM_Frontend_Environment $environment, array $options = null) {
        return $this;
    }
}
