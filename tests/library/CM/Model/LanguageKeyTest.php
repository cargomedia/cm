<?php

class CM_Model_LanguageKeyTest extends CMTest_TestCase {

    public function testExists() {
        $this->assertFalse(CM_Model_LanguageKey::exists('foo'));
        CM_Model_LanguageKey::create('foo');
        $this->assertTrue(CM_Model_LanguageKey::exists('foo'));
    }

    public function testDelete() {
        $language = CM_Model_Language::create('Foo', 'foo', true);
        $language->setTranslation('foo', 'bar');
        $this->assertSame(array('foo' => array('value' => 'bar', 'variables' => array())), $language->getTranslations()->getAssociativeArray());

        $languageKey = CM_Model_LanguageKey::findByName('foo');
        $languageKey->delete();

        $this->assertSame(array(), $this->_language->getTranslations()->getAssociativeArray());
        $this->assertSame(0, CM_Db_Db::count('cm_model_languageKey', array('name' => 'foo')));
        $this->assertSame(0, CM_Db_Db::count('cm_languageValue', array(
            'languageKeyId' => $languageKey->getId(),
            'languageId'    => $language->getId(),
        )));
    }
}
