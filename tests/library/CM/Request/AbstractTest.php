<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_Request_AbstractTest extends TestCase {

	public function tearDown() {
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

	public function testGetLanguageLoggedUser() {
		$user = TH::createUser();
		$request = $this->_prepareRequest('/', null, $user);
		// No language at all
		$this->assertSame($request->getLanguage(), null);

		// Getting default language, user has no language
		$defaultLanguage = TH::createLanguage();
		$this->assertModelEquals($request->getLanguage(), $defaultLanguage);

		// Getting user language
		$anotherUserLanguage = TH::createLanguage();
		$user->setLanguage($anotherUserLanguage);
		$this->assertModelEquals($request->getLanguage(), $anotherUserLanguage);
	}

	public function testGetLanguageGuest() {
		$request = $this->_prepareRequest('/');
		// No language at all
		$this->assertSame($request->getLanguage(), null);

		// Getting default language (guest has no language)
		$defaultLanguage = TH::createLanguage();
		$this->assertModelEquals($request->getLanguage(), $defaultLanguage);
	}

	public function testGetLanguageByUrl() {
		/** @var CM_Site_Abstract $site */
		$site = $this->getMockForAbstractClass('CM_Site_Abstract');
		$request = $this->_prepareRequest('/de/home');
		$site->rewrite($request);
		$this->assertNull($request->getLanguage());
		CM_CacheLocal::flush(); // Need to flush CM_Paging_Languages_Enabled() cache

		TH::createLanguage('en'); // default language
		$urlLanguage = TH::createLanguage('de');
		$request = $this->_prepareRequest('/de/home');
		$site->rewrite($request);
		$this->assertModelEquals($request->getLanguage(), $urlLanguage);
	}

	public function testGetLanguageByBrowser() {
		$defaultLanguage = TH::createLanguage('en');
		$browserLanguage = TH::createLanguage('de');
		$this->assertModelEquals(CM_Model_Language::findDefault(), $defaultLanguage);
		$request = $this->_prepareRequest('/', array('Accept-Language' => 'de'));
		$this->assertModelEquals($request->getLanguage(), $browserLanguage);
		$request = $this->_prepareRequest('/', array('Accept-Language' => 'pl'));
		$this->assertModelEquals($request->getLanguage(), $defaultLanguage);
	}

	/**
	 * @param string             $uri
	 * @param array|null         $additionalHeaders
	 * @param CM_Model_User|null $user
	 * @return CM_Request_Abstract
	 */
	private function _prepareRequest($uri, array $additionalHeaders = null, CM_Model_User $user = null) {
		$headers = array('Host' => 'example.com', 'Connection' => 'keep-alive');
		if ($additionalHeaders) {
			$headers = array_merge($headers, $additionalHeaders);
		}
		/** @var CM_Request_Abstract $request */
		$request = $this->getMockForAbstractClass('CM_Request_Abstract', array($uri, $headers), '', true, true, true, array('getViewer'));
		$request->expects($this->any())->method('getViewer')->will($this->returnValue($user));
		$this->assertSame($request->getViewer(), $user);
		return $request;
	}


}
