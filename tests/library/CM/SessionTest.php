<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_SessionTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testConstructor() {
		$session = new CM_Session();
		$this->assertTrue(true);

		try {
			new CM_Session('nonexistent');
			$this->fail('Can instantiate nonexistent session.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}

	public function testSetGetDelete() {
		$session = new CM_Session();
		$this->assertNull($session->get('foo'));

		$session->set('foo', 'bar');
		$this->assertSame('bar', $session->get('foo'));

		$session->delete('foo');
		$this->assertNull($session->get('foo'));

		$session->set('bar', array('foo', 'bar'));
		$this->assertEquals(array('foo', 'bar'), $session->get('bar'));
	}

	public function testPersistence() {
		$session = new CM_Session();
		$sessionId = $session->getId();
		unset($session);
		try {
			new CM_Session($sessionId);
			$this->fail('Empty Session stored in db.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
		$session = new CM_Session();
		$sessionId = $session->getId();
		$session->set('foo', 'bar');
		unset($session);
		$session = new CM_Session($sessionId);

		$session->set('bar', array('foo', 'bar'));
		$session->set('foobar', 'foobar');
		$expiration = $session->getExpiration();
		$sessionId = $session->getId();
		TH::timeForward(10);
		unset($session);

		try {
			$session = new CM_Session($sessionId);
			$this->assertTrue(true);
		} catch (CM_Exception_Nonexistent $ex) {
			$this->fail('Session not persistent.');
		}
		$this->assertEquals('bar', $session->get('foo'));
		$this->assertEquals(array('foo', 'bar'), $session->get('bar'));
		$this->assertEquals($expiration + 10, $session->getExpiration());

		//test that session is only persisted when data changed
		CM_Mysql::update(TBL_CM_SESSION, array('data' => serialize(array('foo' => 'foo', 'foobar' => 'foobar'))), array('sessionId' => $session->getId()));
		TH::clearCache();
		unset($session);
		$session = new CM_Session($sessionId);
		$this->assertEquals('foo', $session->get('foo'));

		$session->delete('foobar');
		unset($session);
		$session = new CM_Session($sessionId);
		$this->assertNull($session->get('foobar'));

		//caching
		$session->set('foo', 'foo');
		$sessionId = $session->getId();
		unset($session);
		$session = new CM_Session($sessionId);
		unset($session);

		CM_Mysql::update(TBL_CM_SESSION, array('data' => serialize(array('foo' => 'bar'))), array('sessionId' => $sessionId));
		$session = new CM_Session($sessionId);
		$this->assertEquals('foo', $session->get('foo'));

		$session->delete('foo');
		$this->assertTrue($session->isEmpty());
		unset($session);
		try {
			$session = new CM_Session($sessionId);
			$this->fail('Session not deleted.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}

	public function testRegenerateId() {
		$session = new CM_Session();
		$session->set('foo', 'bar');
		$sessionId = $session->getId();
		unset($session);
		$session = new CM_Session($sessionId);
		$oldSessionId = $session->getId();
		$session->regenerateId();
		$newSessionId = $session->getId();
		unset($session);
		try {
			new CM_Session($oldSessionId);
			$this->fail('Db entry not updated.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
		try {
			$session = new CM_Session($newSessionId);
			$this->assertTrue(true);
		} catch (CM_Exception_Nonexistent $ex) {
			$this->fail('Db entry not updated.');
		}
		$this->assertEquals('bar', $session->get('foo'));
	}

	public function testGc() {
		$session = new CM_Session();
		$sessionId = $session->getId();
		unset($session);
		TH::timeForward(4000);
		CM_Session::deleteExpired();
		try {
			new CM_Session($sessionId);
			$this->fail('Expired Session was not deleted.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}

	}

	public function testLogin() {
		$user = CM_Model_User::create();
		$session = new CM_Session();

		$session->setUser($user);
		$this->assertModelEquals($user, $session->getUser(true));
		$this->assertTrue($session->getUser(true)->getOnline());
	}

	public function testLogout() {
		$session = new CM_Session();
		$session->setUser(CM_Model_User::create());
		$user = $session->getUser(true);

		$session->deleteUser();
		$this->assertNull($session->getUser());
		$user->_change();
		$this->assertFalse($user->getOnline());
	}

	public function testGetViewer() {
		$session = new CM_Session();
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

	public function testStart() {
		/** @var CM_Model_User $user */
		$user = CM_Model_User::create();

		$activityStamp1 = time();
		$session = new CM_Session();
		$session->setUser($user);
		$sessionId = $session->getId();
		unset($session);
		$session = new CM_Session($sessionId);
		$this->assertEquals($activityStamp1, $session->getUser(true)->getLatestactivity(), null, 1);

		TH::timeForward(CM_Session::ACTIVITY_EXPIRATION / 10);
		$session = new CM_Session($sessionId);
		$session->start();
		$this->assertEquals($activityStamp1, $session->getUser(true)->getLatestactivity(), null, 1);

		CM_Mysql::update(TBL_CM_SESSION, array('data' => serialize(array('userId' => $user->getId(), 'foo' => 'bar'))));
		unset($session);
		TH::clearCache();

		TH::timeForward(CM_Session::ACTIVITY_EXPIRATION / 2);
		$activityStamp2 = time();
		$session = new CM_Session($sessionId);
		$session->start();
		$this->assertEquals($activityStamp2, $session->getUser(true)->getLatestactivity(), null, 1);
		TH::timeForward($session->getLifetime() / 2);
		$session->start();

		$this->assertEquals('bar', $session->get('foo'));
		CM_Mysql::update(TBL_CM_SESSION, array('data' => serialize(array('userId' => $user->getId(), 'foo' => 'foo'))));
		unset($session);
		TH::clearCache();

		$session = new CM_Session($sessionId);
		$this->assertEquals('bar', $session->get('foo'));
	}

	public function testExpiration() {
		$session = new CM_Session();
		$session->set('foo', 'bar');
		$sessionId = $session->getId();
		unset($session);
		$session = new CM_Session($sessionId);
		$this->assertEquals(time() + CM_Session::LIFETIME_DEFAULT, $session->getExpiration(), null, 1);

		$session->setLifetime(10 * 86400);
		unset($session);
		$session = new CM_Session($sessionId);
		$this->assertEquals(time() + 10 * 86400, $session->getExpiration(), null, 1);

		$session->setLifetime();
		$sessionId = $session->getId();
		unset($session);
		$session = new CM_Session($sessionId);
		$this->assertEquals(time() + CM_Session::LIFETIME_DEFAULT, $session->getExpiration(), null, 1);
	}
}
