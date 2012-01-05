<?php
require_once dirname(__FILE__) . '/../../../TestCase.php';

class CM_Model_UserTest extends TestCase {
	public static function setupBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testGetCreated() {
		$time = time();
		$user = TH::createUser();
		$this->assertGreaterThanOrEqual($time, $user->getCreated());
	}

	public function testGetLatestactivity() {
		$user = TH::createUser();
		$time = $user->getLatestactivity();
		TH::timeForward(1);
		$user->updateLatestactivity();
		$this->assertGreaterThan($time, $user->getLatestactivity());
	}

	public function testGetSetOnline() {
		$user = TH::createUser();
		$this->assertFalse($user->getOnline());
		$user->setOnline();
		$this->assertTrue($user->getOnline());
		$user->setOnline(false);
		$this->assertFalse($user->getOnline());
	}

	public function testGetPreferences() {
		$user = TH::createUser();
		$this->assertInstanceOf('CM_ModelAsset_User_Preferences', $user->getPreferences());
	}

	public function testGetRoles() {
		$user = TH::createUser();
		$this->assertInstanceOf('CM_ModelAsset_User_Roles', $user->getRoles());
	}

	public function testGetSetVisible() {
		$user = TH::createUser();
		try {
			$user->setVisible();
			$this->fail('Able to modify visibility of a user that is offline.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertTrue(true);
		}
		$user->setOnline();
		$this->assertTrue($user->getVisible());
		$user->setVisible(false);
		$this->assertFalse($user->getVisible());
		$user->setVisible(true);
		$this->assertTrue($user->getVisible());
	}

	public function testCreate() {
		$user = CM_Model_User::create();
		$this->assertRow(TBL_CM_USER, array('userId' => $user->getId()));
	}

	public function testDelete() {
		$user = TH::createUser();
		$user->delete();
		try {
			new CM_Model_User($user->getId());
			$this->fail('User not deleted.');
		} catch(CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}

	}

}
