<?php

class CM_Model_UserTest extends CMTest_TestCase {

	public static function setupBeforeClass() {
	}

	public function testGetCreated() {
		$time = time();
		$user = CMTest_TH::createUser();
		$this->assertGreaterThanOrEqual($time, $user->getCreated());
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
		$site = CM_Site_Abstract::factory();
		/** @var CM_Model_User $user */
		$user = CM_Model_User::create(array('site' => $site));
		$this->assertEquals($site, $user->getSite());
	}

	public function testCreateWithLanguage() {
		$language = CMTest_TH::createLanguage();
		/** @var CM_Model_User $user */
		$user = CM_Model_User::create(array('language' => $language));
		$this->assertEquals($language, $user->getLanguage());
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
		$siteDefault = CM_Site_Abstract::factory();
		$user = CMTest_TH::createUser();
		$this->assertEquals($siteDefault, $user->getSite());

		$type = $siteDefault->getType() + 1;
		$site = $this->getMockBuilder('CM_Site_Abstract')->setMethods(array('getType'))->getMock();
		$site->expects($this->any())->method('getType')->will($this->returnValue($type));
		CM_Config::get()->CM_Site_Abstract->types[$type] = get_class($site);
		$user->setSite($site);
		$this->assertEquals($site, $user->getSite());
	}

	public function testSetLanguage() {
		$language = CMTest_TH::createLanguage();
		$user = CMTest_TH::createUser();
		$this->assertNotEquals($language, $user->getLanguage());
		$user->setLanguage($language);
		$this->assertEquals($language, $user->getLanguage());
	}

	public function testUpdateLatestActivity() {
		$user = CMTest_TH::createUser();
		$activityStamp1 = time();
		$this->assertSameTime($activityStamp1, $user->getLatestactivity());
		CMTest_TH::timeForward(CM_Model_User::ACTIVITY_EXPIRATION / 4);
		$user->updateLatestactivity();
		$this->assertSameTime($activityStamp1, $user->getLatestactivity());
		CMTest_TH::timeForward(CM_Model_User::ACTIVITY_EXPIRATION / 10);
		$activityStamp2 = time();
		$user->updateLatestactivity();
		$this->assertSameTime($activityStamp2, $user->getLatestactivity());
	}
}
