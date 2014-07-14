<?php

class CM_Model_LanguageTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testSetGetTranslation() {
        $language = CM_Model_Language::create('English', 'en', true);
        $this->assertSame('keyFirst', $language->getTranslation('keyFirst'));
        $this->assertSame(array('keyFirst' => array('value'     => null,
                                                    'variables' => array())), $language->getTranslations()->getAssociativeArray());

        $language->getTranslation('keyFirst'); // Fill APC
        $language->setTranslation('keyFirst', 'abc');
        $this->assertSame('abc', $language->getTranslation('keyFirst'));
        $this->assertSame(array('keyFirst' => array('value'     => 'abc',
                                                    'variables' => array())), $language->getTranslations()->getAssociativeArray());

        $this->assertSame('abc', $language->getTranslation('keyFirst', array('variable')));
        $this->assertSame(array('keyFirst' => array('value'     => 'abc',
                                                    'variables' => array('variable'))), $language->getTranslations()->getAssociativeArray());

        $language->getTranslation('keyFirst'); // Fill APC
        $language->setTranslation('keyFirst', 'xyz', array('another'));
        $this->assertSame(array('keyFirst' => array('value'     => 'xyz',
                                                    'variables' => array('another'))), $language->getTranslations()->getAssociativeArray());

        $language->getTranslation('keyFirst');
        $this->assertSame(array('keyFirst' => array('value'     => 'xyz',
                                                    'variables' => array('another'))), $language->getTranslations()->getAssociativeArray());
    }

    public function testSetGetTranslationWithoutLocalCache() {
        $language = CM_Model_Language::create('English', 'en', true);
        $this->assertSame('keyFirst', $language->getTranslation('keyFirst', null, true));
        $this->assertSame(array('keyFirst' => array('value'     => null,
                                                    'variables' => array())), $language->getTranslations()->getAssociativeArray());

        $language->getTranslation('keyFirst'); // Fill APC
        $language->setTranslation('keyFirst', 'abc');
        $this->assertSame('abc', $language->getTranslation('keyFirst', null, true));
        $this->assertSame(array('keyFirst' => array('value'     => 'abc',
                                                    'variables' => array())), $language->getTranslations()->getAssociativeArray());
    }

    public function testCreate() {
        $language = CM_Model_Language::create('Deutsch', 'de', true);

        $this->assertInstanceOf('CM_Model_Language', $language);
        $this->assertSame('Deutsch', $language->getName());
        $this->assertSame('de', $language->getAbbreviation());
        $this->assertTrue($language->getEnabled());
        $this->assertNull($language->getBackup());
    }

    public function testCreateWithDuplicateAbbreviation() {
        $language = CM_Model_Language::create('English', 'en', true);
        try {
            CM_Model_Language::create('Another one', $language->getAbbreviation(), true);
            $this->fail('Could create language with duplicate abbreviation');
        } catch (CM_Exception $e) {
            $this->assertContains('Duplicate entry', $e->getMessage());
        }
    }

    public function testDelete() {
        $language = CM_Model_Language::create('English', 'en', true);
        $backedUpLanguage = CM_Model_Language::create('German', 'de', true, $language);
        $this->assertEquals($language, $backedUpLanguage->getBackup());

        $language->delete();
        $this->assertNull(CM_Model_Language::findByAbbreviation('en'));
        $this->assertNull(CM_Model_Language::findByAbbreviation('de')->getBackup());
    }

    public function testSetAbbreviationDuplicate() {
        CM_Model_Language::create('Polish', 'pl', true);
        try {
            CM_Model_Language::create('Another', 'pl', true);
            $this->fail('Could set language with duplicate abbreviation');
        } catch (CM_Exception $e) {
            $this->assertContains('Duplicate entry', $e->getMessage());
        }
    }

    public function testFindByAbbreviation() {
        $language = CM_Model_Language::create('English', 'en', true);
        $this->assertEquals($language, CM_Model_Language::findByAbbreviation($language->getAbbreviation()));
        $this->assertNull(CM_Model_Language::findByAbbreviation('random-not-existing-abbreviation'));
    }

    public function testGetTranslationWithBackup() {
        $language = CM_Model_Language::create('English', 'en', true);
        $backedUpLanguage = CM_Model_Language::create('Polish', 'pl', true, $language);
        $language->setTranslation('phrase', 'abc');
        $this->assertSame('abc', $backedUpLanguage->getTranslation('phrase'));
    }

    public function testIsBackingUp() {
        $language = CM_Model_Language::create('English', 'en', true);
        $backedUpLanguage = CM_Model_Language::create('Polish', 'pl', true, $language);
        $this->assertTrue($language->isBackingUp($backedUpLanguage));
        $this->assertFalse($backedUpLanguage->isBackingUp($language));
    }

    public function testFindDefault() {
        $language = CM_Model_Language::create('English', 'en', true);
        $this->assertEquals($language, CM_Model_Language::findDefault());

        $language->setEnabled(false);
        $this->assertEquals($language, CM_Model_Language::findDefault());

        CM_Cache_Local::getInstance()->flush();
        $this->assertNull(CM_Model_Language::findDefault());
    }

    public function testGetTranslationWithDifferentVariableNamesAndKeysLoop() {
        $language = CM_Model_Language::create('English', 'en', true);
        for ($i = 0; $i < 5; $i++) {
            $language->getTranslation('myKey', array('oneVariable'), true);
            $language->getTranslation('MyKey', array('oneVariable', 'secondOne'), true);
            $language->getTranslation('myKéy', array('oneVariable', 'thirdOne'), true);
        }
        $this->assertTrue(true);
    }
}
