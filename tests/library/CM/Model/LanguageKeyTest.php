<?php

class CM_Model_LanguageKeyTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        $language = CMTest_TH::createLanguage('foo');
        $this->assertCount(0, $language->getTranslations());
        $languageKey = CM_Model_LanguageKey::create('foo', ['bar']);
        $this->assertTrue(CM_Model_LanguageKey::exists('foo'));
        $this->assertCount(1, $language->getTranslations());
        $this->assertSame(['bar'], $languageKey->getVariables());
        $this->assertSame('0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33', $languageKey->getNameHash());
    }

    public function testCreateDuplicate() {
        $languageKeyFirst = CM_Model_LanguageKey::create('foo', ['bar']);
        $languageKeySecond = CM_Model_LanguageKey::create('foo', ['foo']);

        $this->assertEquals($languageKeyFirst, $languageKeySecond);
        $this->assertEquals(['bar'], $languageKeySecond->getVariables());
    }

    public function testSetGetVariables() {
        $languageKey = CM_Model_LanguageKey::create('foo');
        $this->assertSame([], $languageKey->getVariables());
        $this->assertSame(0, $this->forceInvokeMethod($languageKey, '_getUpdateCount'));

        $languageKey->setVariables(['foo']);
        $this->assertSame(['foo'], $languageKey->getVariables());
        $this->assertSame(1, $this->forceInvokeMethod($languageKey, '_getUpdateCount'));

        $languageKey->setVariables(['foo']);
        $this->assertSame(['foo'], $languageKey->getVariables());
        $this->assertSame(1, $this->forceInvokeMethod($languageKey, '_getUpdateCount'));

        $languageKey->setVariables(['foo', 'bar']);
        $this->assertSame(['bar', 'foo'], $languageKey->getVariables());
        $this->assertSame(2, $this->forceInvokeMethod($languageKey, '_getUpdateCount'));

        $languageKey->setVariables(['bar', 'foo']);
        $this->assertSame(['bar', 'foo'], $languageKey->getVariables());
        $this->assertSame(2, $this->forceInvokeMethod($languageKey, '_getUpdateCount'));

        $languageKey->setVariables(null);
        $this->assertSame([], $languageKey->getVariables());
        $this->assertSame(3, $this->forceInvokeMethod($languageKey, '_getUpdateCount'));
    }

    public function testSetVariablesWithDifferentVariablesLoop() {
        $languageKey = CM_Model_LanguageKey::create('foo');
        for ($i = 0; $i < CM_Model_LanguageKey::MAX_UPDATE_COUNT; $i++) {
            $languageKey->setVariables(['variable' . $i]);
        }
        try {
            $languageKey->setVariables(['variable']);
            $this->fail('Did not throw exception after ' . (CM_Model_LanguageKey::MAX_UPDATE_COUNT) . ' changes');
        } catch (CM_Exception_Invalid $e) {
            $this->assertContains('`foo`', $e->getMessage());
        }
    }

    public function testDelete() {
        $language = CM_Model_Language::create('Foo', 'foo', true);
        $language->setTranslation('foo', 'bar');
        $this->assertSame(array('foo' => array('value' => 'bar', 'variables' => array())), $language->getTranslations()->getAssociativeArray());

        $languageKey = CM_Model_LanguageKey::findByName('foo');
        $languageKey->delete();

        $this->assertSame(array(), $language->getTranslations()->getAssociativeArray());
        $this->assertSame(0, CM_Db_Db::count('cm_model_languagekey', array('name' => 'foo')));
        $this->assertSame(0, CM_Db_Db::count('cm_languageValue', array(
            'languageKeyId' => $languageKey->getId(),
            'languageId'    => $language->getId(),
        )));
    }

    public function testFindByName() {
        $languageKey1 = CM_Model_LanguageKey::create('foo');
        $this->assertEquals($languageKey1, CM_Model_LanguageKey::findByName('foo'));

        $this->assertNull(CM_Model_LanguageKey::findByName('nonexistent'));
    }

    public function testReplace() {
        $languageKey = CM_Model_LanguageKey::create('foo', ['bar']);
        $this->assertSame(['bar'], $languageKey->getVariables());

        $this->assertRow('cm_model_languagekey', ['name' => 'foo'], 1);
        $languageKeyReplaced = CM_Model_LanguageKey::replace('foo', ['foo']);
        $this->assertRow('cm_model_languagekey', ['name' => 'foo'], 1);
        $this->assertEquals($languageKey, $languageKeyReplaced);
        $this->assertSame(['foo'], $languageKeyReplaced->getVariables());

        $this->assertRow('cm_model_languagekey', ['name' => 'bar'], 0);
        $languageKeyCreated = CM_Model_LanguageKey::replace('bar', ['bar']);
        $this->assertRow('cm_model_languagekey', ['name' => 'bar'], 1);
        $this->assertSame(['bar'], $languageKeyCreated->getVariables());
    }

    public function testExists() {
        $this->assertFalse(CM_Model_LanguageKey::exists('foo'));
        CM_Model_LanguageKey::create('foo');
        $this->assertTrue(CM_Model_LanguageKey::exists('foo'));
    }

    public function testDeleteByName() {
        CM_Model_LanguageKey::create('foo');
        $this->assertTrue(CM_Model_LanguageKey::exists('foo'));
        CM_Model_LanguageKey::deleteByName('foo');
        $this->assertFalse(CM_Model_LanguageKey::exists('foo'));
        CM_Model_LanguageKey::deleteByName('foo');
    }

    public function testIncreaseUpdateCountNewDeploy() {
        $now = time();
        $languageKey = $this->mockObject('CM_Model_LanguageKey');
        $languageKey->mockMethod('_getDeployVersion')->set($now + 1);
        /** @var CM_Model_LanguageKey $languageKey */
        $languageKey->_set([
            'updateCountResetVersion' => $now,
            'updateCount'             => 5,
        ]);
        CMTest_TH::callProtectedMethod($languageKey, '_increaseUpdateCount');
        $this->assertSame($now + 1, $languageKey->_get('updateCountResetVersion'));
        $this->assertSame(1, $languageKey->_get('updateCount'));
    }

    public function testGetTreeCaching() {
        $this->assertNull(CM_Model_LanguageKey::getTree()->findNodeById('.foo'));
        CM_Model_LanguageKey::create('.foo');
        $this->assertNotNull(CM_Model_LanguageKey::getTree()->findNodeById('.foo'));
    }

    public function testSetName() {
        $language = CMTest_TH::createLanguage();
        $key = CM_Model_LanguageKey::create('foo');
        $this->assertSame('foo', $key->getName());

        $key->setName('bar');
        $this->assertSame('bar', $key->getName());
        $this->assertTrue(Functional\some($language->getTranslations()->getItems(), function ($translation) {
            return $translation['key'] === 'bar';
        }));
    }

    public function testSetNameDuplicateHash() {
        $key1 = CM_Model_LanguageKey::create('foo');
        $key2 = CM_Model_LanguageKey::create('bar');

        $this->assertSame('foo', $key1->getName());
        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($key1) {
            $key1->setName('bar');
        });
        $this->assertInstanceOf(CM_Exception_Invalid::class, $exception);
        $this->assertSame('Duplicate languageKey name', $exception->getMessage());
        $this->assertSame(['name' => 'bar', 'hash' => $key2->getNameHash()], $exception->getMetaInfo());
    }
}
