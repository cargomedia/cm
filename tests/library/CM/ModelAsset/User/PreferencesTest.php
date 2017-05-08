<?php

class CM_ModelAsset_User_PreferencesTest extends CMTest_TestCase {

    public function tearDown() {
        CM_Db_Db::truncate('cm_user_preferenceDefault');
    }

    public function testSetDefault() {
        $this->assertSame(array(), CM_ModelAsset_User_Preferences::getDefaults());

        CM_ModelAsset_User_Preferences::setDefault('foo', 'bar', true, false);
        CM_Cache_Local::getInstance()->flush();
        $preferences = CM_ModelAsset_User_Preferences::getDefaults();
        $this->assertCount(1, $preferences);
        $this->assertTrue($preferences['foo']['bar']['value']);
        $this->assertFalse($preferences['foo']['bar']['configurable']);
        $id = $preferences['foo']['bar']['id'];

        CM_ModelAsset_User_Preferences::setDefault('foo', 'bar', false, false);
        CM_Cache_Local::getInstance()->flush();
        $preferences = CM_ModelAsset_User_Preferences::getDefaults();
        $this->assertCount(1, $preferences);
        $this->assertSame($id, $preferences['foo']['bar']['id']);
        $this->assertFalse($preferences['foo']['bar']['value']);

        CM_ModelAsset_User_Preferences::setDefault('bar', 'foo', false, false);
        CM_Cache_Local::getInstance()->flush();
        $preferences = CM_ModelAsset_User_Preferences::getDefaults();
        $this->assertCount(2, $preferences);

    }

    public function testGetSet() {
        CM_ModelAsset_User_Preferences::setDefault('test', 'foo', false, true);
        CM_ModelAsset_User_Preferences::setDefault('test', 'bar', true, true);

        $preferences = CMTest_TH::createUser()->getPreferences();
        $defaults = $preferences->getDefaults();
        $sections = array_keys($defaults);
        $section = reset($sections);
        $keys = array_keys($defaults[$section]);
        $key = reset($keys);

        $this->assertEquals($defaults[$section][$key]['value'], $preferences->get($section, $key));

        $preferences->set($section, $key, true);
        $this->assertEquals(true, $preferences->get($section, $key));
        $preferences->set($section, $key, false);
        $this->assertEquals(false, $preferences->get($section, $key));
    }

    public function testReset() {
        CM_ModelAsset_User_Preferences::setDefault('test', 'foo', false, true);
        CM_ModelAsset_User_Preferences::setDefault('test', 'bar', true, true);
        $preferences = CMTest_TH::createUser()->getPreferences();
        $defaults = $preferences->getDefaults();
        $sections = array_keys($defaults);
        $section = reset($sections);
        $keys = array_keys($defaults[$section]);
        $key = reset($keys);

        $preferences->set($section, $key, !$preferences->get($section, $key));
        $this->assertNotEquals($preferences->getAll(), $defaults);

        $preferences->reset();
        $this->assertEquals($preferences->getAll(), $defaults);
    }

    public function testInvalidatedModel() {
        CM_ModelAsset_User_Preferences::setDefault('test', 'foo', false, true);
        CM_ModelAsset_User_Preferences::setDefault('test', 'bar', true, true);
        $user = CMTest_TH::createUser();
        $user->_change();
        $user->getPreferences()->getAll();
        $user->getLatestActivity();
    }
}
