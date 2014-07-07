<?php

class CM_Paging_Translation_LanguageTest extends CMTest_TestCase {

    public function testRemove() {
        $language = CM_Model_Language::create('Foo', 'foo', true);
        $paging = $language->getTranslations();
        $paging->set('phrase', 'abc');
        $this->assertSame('abc', $paging->get('phrase', null, true));
        $paging->remove('phrase');
        $this->assertSame('phrase', $paging->get('phrase', null, true));
    }
}
