<?php

class CM_Paging_Translation_Language_AbstractTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testTrailingWhitespaceInLanguageKeyName() {
        CM_Db_Db::insert('cm_model_languagekey', ['name'], [['foo '],['foo']]);

        $language = CM_Model_Language::create('Foo', 'foo', true);
        $language->getTranslations()->getAssociativeArray();
        $this->assertEquals(['foo ', 'foo'], array_keys($language->getTranslations()->getAssociativeArray()));
        $this->assertCount(2, $language->getTranslations());
    }
}
