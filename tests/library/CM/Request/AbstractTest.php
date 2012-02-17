<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_Request_AbstractTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testGetViewer() {
		$user = TH::createUser();
		$uri = '/';
		$headers = array('Host' => 'example.ch', 'Connection' => 'keep-alive');
		$mock = $this->getMockForAbstractClass('CM_Request_Abstract', array($uri, $headers));
		$this->assertNull($mock->getViewer());

		$headers = array('Host' => 'example.ch', 'Connection' => 'keep-alive', 'Cookie' => 'sessionId=a1d2726e5b3801226aafd12fd62496c8');
		$mock = $this->getMockForAbstractClass('CM_Request_Abstract', array($uri, $headers));
		try {
			$mock->getViewer(true);
			$this->fail();
		} catch (CM_Exception_AuthRequired $ex) {
			$this->assertTrue(true);
		}

		$session = new CM_Session();
		$session->setUser($user);
		$headers = array('Host' => 'example.ch', 'Connection' => 'keep-alive', 'Cookie' => 'sessionId=' . $session->getId());
		unset($session);

		$mock = $this->getMockForAbstractClass('CM_Request_Abstract', array($uri, $headers));
		$this->assertModelEquals($user, $mock->getViewer(true));

		$user2 = TH::createUser();
		$mock = $this->getMockForAbstractClass('CM_Request_Abstract', array($uri, $headers, $user2));
		$this->assertModelEquals($user2, $mock->getViewer(true));
	}

	public function testGetCookie() {
		$uri = '/';
		$headers = array('Host' => 'example.ch', 'Connection' => 'keep-alive', 'Cookie' => ';213q;213;=foo=hello;bar=tender;  adkhfa ; asdkf===fsdaf');
		$mock = $this->getMockForAbstractClass('CM_Request_Abstract', array($uri, $headers));
		$this->assertEquals('hello', $mock->getCookie('foo'));
		$this->assertEquals('tender', $mock->getCookie('bar'));
		$this->assertNull($mock->getCookie('asdkf'));
	}


}
