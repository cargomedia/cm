<?php
require_once dirname(__FILE__) . '/../../../../TestCase.php';

class CM_ModelAsset_User_PreferencesTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testGetSet() {
		$preferences = TH::createUser()->getPreferences();
		$defaults = $preferences->getAll();
		$section = reset(array_keys($defaults));
		$key = reset(array_keys($defaults[$section]));
		
		$this->assertEquals($defaults[$section][$key]['value'], $preferences->get($section, $key));
		
		$preferences->set($section, $key, true);
		$this->assertEquals(true, $preferences->get($section, $key));
		$preferences->set($section, $key, false);
		$this->assertEquals(false, $preferences->get($section, $key));
	}
	
	public function testReset() {
		$preferences = TH::createUser()->getPreferences();
		$defaults = $preferences->getAll();
		$section = reset(array_keys($defaults));
		$key = reset(array_keys($defaults[$section]));
		
		$preferences->set($section, $key, !$preferences->get($section, $key));
		$this->assertNotEquals($preferences->getAll(), $defaults);
		
		$preferences->reset();
		$this->assertEquals($preferences->getAll(), $defaults);
	}

	public function testInvalidatedModel() {
		$user = TH::createUser();
		$user->_change();
		$user->getPreferences()->getAll();
		$user->getLatestactivity();
	}
}
