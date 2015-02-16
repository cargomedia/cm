<?php

class CM_Paging_Language_EnabledTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testEnabled() {
        $english = CM_Model_Language::create('English', 'en', true);
        $italian = CM_Model_Language::create('Italian', 'it', true);
        $german = CM_Model_Language::create('German', 'de', true);
        $french = CM_Model_Language::create('French', 'fr', true);
        $svenska = CM_Model_Language::create('Svenska', 'sv', false);

        $paging = new CM_Paging_Language_Enabled();
        $this->assertEquals([$english, $french, $german, $italian], $paging->getItems());
        $this->assertNotContains($svenska, $paging->getItems());
    }
}
