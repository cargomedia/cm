<?php
require_once dirname(__FILE__) . '/../../../../TestCase.php';

class CM_ModelAsset_User_RolesTest extends TestCase {
	
	const ROLE_A = 10;
	const ROLE_B = 11;
	const ROLE_C = 12;

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testAdd() {
		$user = TH::createUser();
		$user->getRoles()->add(self::ROLE_A, 1000);
		$this->assertTrue($user->getRoles()->contains(self::ROLE_A));
		$user->getRoles()->add(self::ROLE_A, 1000);
		$user->getRoles()->add(self::ROLE_B, 1000);
		$this->assertTrue($user->getRoles()->contains(self::ROLE_B));
		$this->assertEquals(2000, $user->getRoles()->getExpirationStamp(self::ROLE_A) - $user->getRoles()->getStartStamp(self::ROLE_A));
		$user->getRoles()->add(self::ROLE_A);
		$this->assertTrue($user->getRoles()->contains(self::ROLE_A));
		$this->assertFalse((boolean) $user->getRoles()->getExpirationStamp(self::ROLE_A));
		$user->getRoles()->add(self::ROLE_A, 10000);
		$this->assertFalse((boolean) $user->getRoles()->getExpirationStamp(self::ROLE_A));
	}
	
	public function testDelete() {
		$user = TH::createUser();
		$user->getRoles()->add(self::ROLE_A, 1000);
		$this->assertTrue($user->getRoles()->contains(self::ROLE_A));
		$user->getRoles()->add(self::ROLE_B, 1000);
		$user->getRoles()->delete(self::ROLE_A);
		$this->assertTrue($user->getRoles()->contains(self::ROLE_B));
		$this->assertFalse($user->getRoles()->contains(self::ROLE_A));
	}
	
	public function testClean() {
		CM_EventHandler::getInstance()->unbind('roleExpired');

		TH::clearEnv();
		$user1 = TH::createUser();
		$user2 = TH::createUser();
		$user1->getRoles()->add(self::ROLE_A, 2000);
		$user1->getRoles()->add(self::ROLE_C);
		$user1->getRoles()->add(self::ROLE_B, 1000);
		$user2->getRoles()->add(self::ROLE_A, 2000);
		$user2->getRoles()->add(self::ROLE_B, 1000);
		$this->assertTrue($user1->getRoles()->contains(self::ROLE_B));
		TH::timeForward(1500);
		CM_ModelAsset_User_Roles::deleteOld($user1);
		$user1->_change();
		$user2->_change();
		
		$this->assertFalse($user1->getRoles()->contains(self::ROLE_B));
		$this->assertRow(TBL_CM_ROLE, array('userId' => $user2->getId(), 'role' => self::ROLE_B));
		$this->assertFalse($user2->getRoles()->contains(self::ROLE_B));
		$this->assertTrue($user2->getRoles()->contains(self::ROLE_A));
		CM_ModelAsset_User_Roles::deleteOld();
		$user1->_change();
		$user2->_change();
		
		$this->assertNotRow(TBL_CM_ROLE, array('userId' => $user2->getId(), 'role' => self::ROLE_B));
		$this->assertTrue($user2->getRoles()->contains(self::ROLE_A));
		$this->assertTrue($user1->getRoles()->contains(self::ROLE_A));
		$this->assertTrue($user1->getRoles()->contains(self::ROLE_C));
	}
	
	public function test_Get() {
		$user = TH::createUser();
		$user->getRoles()->add(self::ROLE_A, 2000);
		$stamps = CM_Mysql::select(TBL_CM_ROLE, array('startStamp', 'expirationStamp'), array('userId' => $user->getId()))->fetchAssoc();
		$this->assertEquals($stamps['startStamp'], $user->getRoles()->getStartStamp(self::ROLE_A));
		$this->assertEquals($stamps['expirationStamp'], $user->getRoles()->getExpirationStamp(self::ROLE_A));
	}
}
