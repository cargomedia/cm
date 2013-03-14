<?php

class CM_ModelAsset_User_PreferencesTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {
		CM_Mysql::insert(TBL_CM_USER_PREFERENCEDEFAULT, array('section' => 'test', 'key' => 'foo', 'defaultValue' => 0, 'configurable' => 1));
		CM_Mysql::insert(TBL_CM_USER_PREFERENCEDEFAULT, array('section' => 'test', 'key' => 'bar', 'defaultValue' => 1, 'configurable' => 1));
	}

	public static function tearDownAfterClass() {
		CM_Db_Db::truncate(TBL_CM_USER_PREFERENCEDEFAULT);
	}

	public function testGetSet() {
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
		$user = CMTest_TH::createUser();
		$user->_change();
		$user->getPreferences()->getAll();
		$user->getLatestactivity();
	}
}
