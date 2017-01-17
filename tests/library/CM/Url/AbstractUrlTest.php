<?php

use CM\Url\AbstractUrl;
use CM\Url\AbsoluteUrl;

class CM_Url_AbstractUrlTest extends CMTest_TestCase {

    public function testGetRebaseUrl() {

        $url = CM_Url_MockUrl::createFromString('/bar?foobar=1');
        $baseUrl = AbsoluteUrl::createFromString('https://foz/baz?fozbaz=2');

        $rebasedUrl = $url->getRebaseUrl($baseUrl);
        $this->assertSame('https', $rebasedUrl->getScheme());
        $this->assertSame('foz', $rebasedUrl->getHost());
        $this->assertSame('/baz/bar', $rebasedUrl->getPath());
        $this->assertSame('foobar=1', $rebasedUrl->getQuery());
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
