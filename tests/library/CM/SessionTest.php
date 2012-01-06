<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_SessionTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testLogin() {
		$user = CM_Model_User::create();
		$session = CM_Session::getInstance(true);

		$session->setUser($user);
		$this->assertModelEquals($user, $session->getUser(true));
		$this->assertTrue($session->getUser(true)->getOnline());
	}

	public function testLogout() {
		$session = CM_Session::getInstance(true);
		$session->setUser(CM_Model_User::create());
		$user = $session->getUser(true);

		$session->deleteUser();
		$this->assertNull($session->getUser());
		$user->_change();
		$this->assertFalse($user->getOnline());
	}

	public function testSetGetDelete() {
		$session = CM_Session::getInstance(true);

		$this->assertNull($session->get('foo'));

		$session->set('foo', 'bar');
		$this->assertSame('bar', $session->get('foo'));

		$session->delete('foo');
		$this->assertNull($session->get('foo'));
	}

	public function testGetViewer() {
		$session = CM_Session::getInstance(true);
		$this->assertNull($session->getUser());
		try {
			$session->getUser(true);
			$this->fail('Should throw exception');
		} catch (CM_Exception_AuthRequired $ex) {
			$this->assertTrue(true);
		}

		/** @var CM_Model_User $user */
		$user = CM_Model_User::create();
		$session->setUser($user);
		$this->assertModelEquals($user, $session->getUser(true));
	}

	public function testLatestactivity() {
		/** @var CM_Model_User $user */
		$user = CM_Model_User::create();

		$activityStamp1 = time();
		$session = CM_Session::getInstance(true);
		$session->setUser($user);
		$this->assertEquals($activityStamp1, $session->getUser(true)->getLatestactivity(), null, 1);

		TH::timeForward(CM_Session::ACTIVITY_EXPIRATION / 10);
		$session = CM_Session::getInstance(true);
		$session->setUser($user);
		$this->assertEquals($activityStamp1, $session->getUser(true)->getLatestactivity(), null, 1);

		TH::timeForward(CM_Session::ACTIVITY_EXPIRATION / 2);
		$activityStamp2 = time();
		$session = CM_Session::getInstance(true);
		$session->setUser($user);
		$this->assertEquals($activityStamp2, $session->getUser(true)->getLatestactivity(), null, 1);
	}
}
