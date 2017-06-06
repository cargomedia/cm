<?php

namespace CM\Test\Url;

use CMTest_TH;
use CMTest_TestCase;
use CM\Url\BaseUrl;

class BaseUrlTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        $url = BaseUrl::create('http://host');
        $this->assertSame(null, $url->getPath());
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame('http://host/', (string) $url);

        $url = BaseUrl::create('http://host/?foo=1#bar');
        $this->assertSame(null, $url->getPath());
        $this->assertSame(null, $url->getPrefix());
        $this->assertSame('http://host/', (string) $url);

        $url = BaseUrl::create('http://host/foo');
        $this->assertSame(null, $url->getPath());
        $this->assertSame('foo', $url->getPrefix());
        $this->assertSame('http://host/foo', (string) $url);

        $url = BaseUrl::create('http://host/foo?bar=1#baz');
        $this->assertSame(null, $url->getPath());
        $this->assertSame('foo', $url->getPrefix());
        $this->assertSame('http://host/foo', (string) $url);

        $url = BaseUrl::create('http://host/foo/bar/baz?some=param#fragment');
        $this->assertSame(null, $url->getPath());
        $this->assertSame('foo/bar/baz', $url->getPrefix());
        $this->assertSame('http://host/foo/bar/baz', (string) $url);

        /** @var \CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () {
            BaseUrl::create('/foo?bar=1#baz');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('BaseUrl::create argument must be an absolute Url', $exception->getMessage());
        $this->assertSame([
            'url' => '/foo?bar=1#baz',
        ], $exception->getMetaInfo());
    }
}
