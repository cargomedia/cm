<?php

namespace CM\Test\Url;

use CMTest_TH;
use CMTest_TestCase;
use CM_Model_Language;
use CM\Url\PageUrl;

class PageUrlTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testParseLanguage() {
        $language = CM_Model_Language::create('english', 'en', true);

        $this->assertSame(null, PageUrl::createFromString('/')->getLanguage());
        $this->assertSame(null, PageUrl::createFromString('/de/foo/bar')->getLanguage());
        $this->assertEquals($language, PageUrl::createFromString('/en/foo/bar')->getLanguage());
    }
}
