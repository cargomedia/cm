<?php

namespace CM\Test\Url;

use CMTest_TestCase;
use CM\Url\AbstractUrl;
use CM\Url\AbsoluteUrl;

class AbstractUrlTest extends CMTest_TestCase {

    public function testGetRebaseUrl() {

        $url = CM_Url_MockUrl::createFromString('/bar?foobar=1');
        $baseUrl = AbsoluteUrl::createFromString('https://foz/baz?fozbaz=2');

        $rebaseUrl = $url->getRebaseUrl($baseUrl);
        $this->assertSame('https', $rebaseUrl->getScheme());
        $this->assertSame('foz', $rebaseUrl->getHost());
        $this->assertSame('/baz/bar', $rebaseUrl->getPath());
        $this->assertSame('foobar=1', $rebaseUrl->getQuery());
        $this->assertSame('https://foz/baz/bar?foobar=1', (string) $rebaseUrl);

        $exception = $this->catchException(function () {
            $url = CM_Url_MockUrl::createFromString('/foo');
            $baseUrl = CM_Url_MockUrl::createFromString('/bar');
            $url->getRebaseUrl($baseUrl);
        });
        $this->assertInstanceOf('InvalidArgumentException', $exception);
        $this->assertSame('The URI components will produce a `CM\Url\AbsoluteUrl` instance in invalid state', $exception->getMessage());
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
