<?php

class CM_Model_UserTest extends CMTest_TestCase {

	public static function setupBeforeClass() {
	}

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testGetCreated() {
		$time = time();
		$user = CMTest_TH::createUser();
		$this->assertGreaterThanOrEqual($time, $user->getCreated());
	}

	public function testGetLatestactivity() {
		$user = CMTest_TH::createUser();
		$time = $user->getLatestactivity();
		CMTest_TH::timeForward(1);
		$user->updateLatestactivity();
		$this->assertGreaterThan($time, $user->getLatestactivity());
	}

	public function testGetSetOnline() {
		$user = CMTest_TH::createUser();
		$this->assertFalse($user->getOnline());
		$user->setOnline();
		$this->assertTrue($user->getOnline());
		$user->setOnline(false);
		$this->assertFalse($user->getOnline());
	}

	public function testGetPreferences() {
		$user = CMTest_TH::createUser();
		$this->assertInstanceOf('CM_ModelAsset_User_Preferences', $user->getPreferences());
	}

	public function testGetRoles() {
		$user = CMTest_TH::createUser();
		$this->assertInstanceOf('CM_ModelAsset_User_Roles', $user->getRoles());
	}

	public function testGetSetVisible() {
		$user = CMTest_TH::createUser();
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

	public function testCreateWithSite() {
		$siteCM = new CM_Site_CM();
		$user = CM_Model_User::create(array('site' => $siteCM));
		$this->assertRow(TBL_CM_USER, array('userId' => $user->getId(), 'site' => $siteCM->getType()));
	}

	public function testDelete() {
		$user = CMTest_TH::createUser();
		$user->delete();
		try {
			new CM_Model_User($user->getId());
			$this->fail('User not deleted.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}

	public function testSetSite() {
		/** @var CM_Model_User $user */
		$user = CM_Model_User::create();
		$this->assertRow(TBL_CM_USER, array('userId' => $user->getId(), 'site' => null));
		$siteCM = new CM_Site_CM();
		$user->setSite($siteCM);
		$this->assertRow(TBL_CM_USER, array('userId' => $user->getId(), 'site' => $siteCM->getType()));
	}
}
