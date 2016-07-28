<?php

class CM_Model_UserTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        $user = CM_Model_User::createStatic();
        $this->assertEquals(time(), $user->getCreated());
        $this->assertEquals(time(), $user->getLatestActivity());
        $this->assertEquals(CM_Site_Abstract::factory(), $user->getSite());
        $this->assertSame(null, $user->getLanguage());
        $this->assertSame(null, $user->getCurrency());
        $this->assertNull($user->getLastSessionSite());
    }

    public function testCreateAllData() {
        CMTest_TH::createDefaultCurrency();
        $site = $this->getMockSite();
        $language = CM_Model_Language::create('English', 'en', true);
        $currency = CM_Model_Currency::create('978', 'EUR');
        $user = CM_Model_User::createStatic([
            'site'     => $site,
            'language' => $language,
            'currency'  => $currency,
        ]);
        $this->assertInternalType('int', $user->getCreated());
        $this->assertEquals(time(), $user->getCreated());
        $this->assertEquals(time(), $user->getLatestActivity());
        $this->assertEquals($site, $user->getSite());
        $this->assertEquals($language, $user->getLanguage());
        $this->assertEquals($currency, $user->getCurrency());
        $this->assertEquals($site, $user->getLastSessionSite());
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

    public function testCreateWithSite() {
        $site = CM_Site_Abstract::factory();
        /** @var CM_Model_User $user */
        $user = CM_Model_User::createStatic(array('site' => $site));
        $this->assertEquals($site, $user->getSite());
    }

    public function testCreateWithLanguage() {
        $language = CMTest_TH::createLanguage();
        /** @var CM_Model_User $user */
        $user = CM_Model_User::createStatic(array('language' => $language));
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

        $site = $this->getMockSite();
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

    public function testUpdateLatestActivityThrottled() {
        $user = CMTest_TH::createUser();
        $activityStamp1 = time();
        $siteDefault = CM_Site_Abstract::factory();
        $this->assertSameTime($activityStamp1, $user->getLatestActivity());
        $this->assertNull($user->getLastSessionSite());
        CMTest_TH::timeForward(CM_Model_User::ACTIVITY_EXPIRATION / 2);
        $user->updateLatestActivityThrottled();
        $this->assertSameTime($activityStamp1, $user->getLatestActivity());
        CMTest_TH::timeForward(CM_Model_User::ACTIVITY_EXPIRATION / 2 + 1);
        $activityStamp2 = time();
        $user->updateLatestActivityThrottled($siteDefault);
        $this->assertSameTime($activityStamp2, $user->getLatestActivity());
        $this->assertEquals($siteDefault, $user->getLastSessionSite());
        $user->_change();
        $this->assertSameTime($activityStamp2, $user->getLatestActivity());
        $this->assertEquals($siteDefault, $user->getLastSessionSite());
    }
}
