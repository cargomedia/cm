<?php

class CM_Splittest_FixtureTest extends CMTest_TestCase {

	public function testCreate() {
		new CM_Splittest_Fixture(new CM_Request_Post('/foo/null'));
		new CM_Splittest_Fixture(CMTest_TH::createUser());
		try {
			new CM_Splittest_Fixture(CMTest_TH::createSession());
			$this->fail('Should not be able to create a fixture from a session');
		} catch (CM_Exception_Invalid $e) {
			$this->assertContains('Invalid fixture type', $e->getMessage());
		}
	}

	public function testGetFixtureTye() {
		$fixtureRequestClient = new CM_Splittest_Fixture(new CM_Request_Post('/foo/null'));
		$fixtureUser = new CM_Splittest_Fixture(CMTest_TH::createUser());
		$this->assertSame(CM_Splittest_Fixture::TYPE_REQUEST_CLIENT, $fixtureRequestClient->getFixtureType());
		$this->assertSame(CM_Splittest_Fixture::TYPE_USER, $fixtureUser->getFixtureType());
	}

	public function testGetColumnId() {
		$fixtureRequestClient = new CM_Splittest_Fixture(new CM_Request_Post('/foo/null'));
		$fixtureUser = new CM_Splittest_Fixture(CMTest_TH::createUser());
		$this->assertSame('requestClientId', $fixtureRequestClient->getColumnId());
		$this->assertSame('userId', $fixtureUser->getColumnId());
	}

	public function testGetId() {
		$request = new CM_Request_Post('/foo/null');
		$user = CMTest_TH::createUser();
		$fixtureRequestClient = new CM_Splittest_Fixture($request);
		$fixtureUser = new CM_Splittest_Fixture($user);
		$this->assertSame($request->getClientId(), $fixtureRequestClient->getId());
		$this->assertSame($user->getId(), $fixtureUser->getId());
	}

	public function testSetUserForRequestClient_userGetsFirstRequestClientVariation() {
		CM_Config::get()->CM_Model_Splittest->withoutPersistence = false;

		/** @var CM_Model_Splittest_RequestClient_Mock $splittestRequestClient */
		$splittestRequestClient = CM_Model_Splittest_RequestClient_Mock::create(array('name' => 'foo', 'variations' => range(1, 100)));
		/** @var CM_Model_Splittest_User_Mock $splittestUser */
		$splittestUser = CM_Model_Splittest_User_Mock::findId($splittestRequestClient->getId());

		$request1 = new CM_Request_Post('/foo/null');
		$variationRequest1 = $splittestRequestClient->getVariationFixture($request1);
		$userA = CMTest_TH::createUser();
		CM_Splittest_Fixture::setUserForRequestClient($request1, $userA);
		$this->assertSame($variationRequest1, $splittestUser->getVariationFixture($userA));

		for ($i = 0; $i < 10; $i++) {
			$requestNew = new CM_Request_Post('/foo/null');
			$splittestRequestClient->getVariationFixture($requestNew);
			CM_Splittest_Fixture::setUserForRequestClient($requestNew, $userA);
			$this->assertSame($variationRequest1, $splittestUser->getVariationFixture($userA));
		}

		$splittestRequestClient->delete();
		CMTest_TH::clearCache();
	}

	public function testSetUserForRequestClient_usersFromSameClientGetSameVariation() {
		CM_Config::get()->CM_Model_Splittest->withoutPersistence = false;

		/** @var CM_Model_Splittest_RequestClient_Mock $splittestRequestClient */
		$splittestRequestClient = CM_Model_Splittest_RequestClient_Mock::create(array('name' => 'foo', 'variations' => range(1, 100)));
		/** @var CM_Model_Splittest_User_Mock $splittestUser */
		$splittestUser = CM_Model_Splittest_User_Mock::findId($splittestRequestClient->getId());

		$request1 = new CM_Request_Post('/foo/null');
		$variationRequest1 = $splittestRequestClient->getVariationFixture($request1);
		$userA = CMTest_TH::createUser();
		CM_Splittest_Fixture::setUserForRequestClient($request1, $userA);
		$this->assertSame($variationRequest1, $splittestUser->getVariationFixture($userA));

		for ($i = 0; $i < 10; $i++) {
			$userNew = CMTest_TH::createUser();
			CM_Splittest_Fixture::setUserForRequestClient($request1, $userNew);
			$this->assertSame($variationRequest1, $splittestUser->getVariationFixture($userNew));
		}
		$this->assertSame($variationRequest1, $splittestUser->getVariationFixture($userA));

		$splittestRequestClient->delete();
		CMTest_TH::clearCache();
	}

	public function testSetUserForRequestClient_onLogin() {
		CM_Config::get()->CM_Model_Splittest->withoutPersistence = false;

		/** @var CM_Model_Splittest_RequestClient_Mock $splittestRequestClient */
		$splittestRequestClient = CM_Model_Splittest_RequestClient_Mock::create(array('name' => 'foo', 'variations' => range(1, 100)));
		/** @var CM_Model_Splittest_User_Mock $splittestUser */
		$splittestUser = CM_Model_Splittest_User_Mock::findId($splittestRequestClient->getId());

		$request = new CM_Request_Post('/foo/null');
		$variationRequest = $splittestRequestClient->getVariationFixture($request);
		$user = CMTest_TH::createUser();
		$session = $request->getSession();
		$session->setUser($user);
		$this->assertSame($variationRequest, $splittestUser->getVariationFixture($user));

		$splittestRequestClient->delete();
		CMTest_TH::clearCache();
	}

	public function testSetUserForRequestClient_userConversionAfterLogin() {
		CM_Config::get()->CM_Model_Splittest->withoutPersistence = false;

		/** @var CM_Model_Splittest_RequestClient_Mock $splittestRequestClient */
		$splittestRequestClient = CM_Model_Splittest_RequestClient_Mock::create(array('name' => 'foo', 'variations' => array('v')));
		/** @var CM_Model_Splittest_User_Mock $splittestUser */
		$splittestUser = CM_Model_Splittest_User_Mock::findId($splittestRequestClient->getId());

		$variation = $splittestUser->getVariationBest();
		$this->assertSame(0, $variation->getFixtureCount());
		$this->assertSame(0, $variation->getConversionCount());

		$request1 = new CM_Request_Post('/foo/null');
		$splittestRequestClient->getVariationFixture($request1);
		$this->assertSame(1, $variation->getFixtureCount(true));
		$this->assertSame(0, $variation->getConversionCount(true));

		$userA = CMTest_TH::createUser();
		$session = $request1->getSession();
		$session->setUser($userA);
		$this->assertSame(1, $variation->getFixtureCount(true));
		$this->assertSame(0, $variation->getConversionCount(true));

		$splittestUser->setConversion($userA);
		$this->assertSame(1, $variation->getFixtureCount(true));
		$this->assertSame(1, $variation->getConversionCount(true));

		$request2 = new CM_Request_Post('/foo/null');
		$splittestRequestClient->getVariationFixture($request2);
		$this->assertSame(2, $variation->getFixtureCount(true));
		$this->assertSame(1, $variation->getConversionCount(true));

		$request3 = new CM_Request_Post('/foo/null');
		$splittestRequestClient->getVariationFixture($request3);
		$this->assertSame(3, $variation->getFixtureCount(true));
		$this->assertSame(1, $variation->getConversionCount(true));

		$session = $request3->getSession();
		$session->setUser($userA);
		$this->assertSame(3, $variation->getFixtureCount(true));
		$this->assertSame(1, $variation->getConversionCount(true));

		$splittestUser->setConversion($userA);
		$this->assertSame(3, $variation->getFixtureCount(true));
		$this->assertSame(1, $variation->getConversionCount(true));

		$userB = CMTest_TH::createUser();
		$session = $request2->getSession();
		$session->setUser($userB);
		$this->assertSame(3, $variation->getFixtureCount(true));
		$this->assertSame(1, $variation->getConversionCount(true));

		$splittestUser->setConversion($userB);
		$this->assertSame(3, $variation->getFixtureCount(true));
		$this->assertSame(2, $variation->getConversionCount(true));

		$splittestRequestClient->delete();
		CMTest_TH::clearCache();
	}

	public function testSetUserForRequestClient_userConversionBeforeLogin() {
		CM_Config::get()->CM_Model_Splittest->withoutPersistence = false;

		/** @var CM_Model_Splittest_RequestClient_Mock $splittestRequestClient */
		$splittestRequestClient = CM_Model_Splittest_RequestClient_Mock::create(array('name' => 'foo', 'variations' => array('v')));
		/** @var CM_Model_Splittest_User_Mock $splittestUser */
		$splittestUser = CM_Model_Splittest_User_Mock::findId($splittestRequestClient->getId());

		$variation = $splittestUser->getVariationBest();
		$this->assertSame(0, $variation->getFixtureCount(true));
		$this->assertSame(0, $variation->getConversionCount(true));

		$request = new CM_Request_Post('/foo/null');
		$splittestRequestClient->getVariationFixture($request);
		$this->assertSame(1, $variation->getFixtureCount(true));
		$this->assertSame(0, $variation->getConversionCount(true));

		$user = CMTest_TH::createUser();
		$splittestUser->setConversion($user);
		$this->assertSame(1, $variation->getFixtureCount(true));
		$this->assertSame(0, $variation->getConversionCount(true)); // Conversion ignored

		$session = $request->getSession();
		$session->setUser($user);
		$this->assertSame(1, $variation->getFixtureCount(true));
		$this->assertSame(0, $variation->getConversionCount(true));

		$splittestUser->setConversion($user);
		$this->assertSame(1, $variation->getFixtureCount(true));
		$this->assertSame(1, $variation->getConversionCount(true));

		$splittestRequestClient->delete();
		CMTest_TH::clearCache();
	}
}

class CM_Model_Splittest_RequestClient_Mock extends CM_Model_Splittest_RequestClient {

	const TYPE = 1;

	/**
	 * @param CM_Request_Abstract $request
	 * @return string
	 */
	public function getVariationFixture(CM_Request_Abstract $request) {
		return $this->_getVariationFixture(new CM_Splittest_Fixture($request));
	}
}

class CM_Model_Splittest_User_Mock extends CM_Model_Splittest_User {

	const TYPE = 1;

	/**
	 * @param CM_Model_User $user
	 * @return string
	 */
	public function getVariationFixture(CM_Model_User $user) {
		return $this->_getVariationFixture(new CM_Splittest_Fixture($user));
	}
}
