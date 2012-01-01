<?php
require_once dirname(__FILE__) . '/../../../TestCase.php';

class CM_Action_ActionTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testConstruct() {
		$actor = TH::createUser();
		/** @var CM_Action_Abstract $action */
		$action = $this->getMockForAbstractClass('CM_Action_Abstract', array(1, $actor));
		$this->assertInstanceOf('CM_Action_Abstract', $action);
		$this->assertSame(1, $action->getType());
		$this->assertSame($actor, $action->getActor());
		$this->assertNull($action->getIp());

		$actor = 123456; // IP address
		/** @var CM_Action_Abstract $action */
		$action = $this->getMockForAbstractClass('CM_Action_Abstract', array(1, $actor));
		$this->assertInstanceOf('CM_Action_Abstract', $action);
		$this->assertSame(1, $action->getType());
		$this->assertNull($action->getActor());
		$this->assertSame($actor, $action->getIp());

		try {
			$action = $this->getMockForAbstractClass('CM_Action_Abstract', array(1, 'foo'));
			$this->fail("Can instantiate action with actor `foo`");
		} catch (CM_Exception_Invalid $e) {
			$this->assertTrue(true);
		}

		try {
			$action = $this->getMockForAbstractClass('CM_Action_Abstract', array(1, null));
			$this->fail("Can instantiate action with actor `null`");
		} catch (CM_Exception_Invalid $e) {
			$this->assertTrue(true);
		}
	}

	public function testPrepare() {
		$actor = TH::createUser();
		TH::createProfile($actor);	// @todo SK
		/** @var CM_Action_Abstract $action */
		$action = new CM_Action_Mock(1, $actor);
		$action->prepare();

		CM_Mysql::insert(TBL_CM_ACTIONLIMIT, array('entityType' => 1, 'actionType' => 1, 'type' => 1, 'role' => null, 'limit' => 0, 'period' => 0));
		TH::clearCache();
		try {
			$action->prepare();
			$this->fail('Limited action did not throw exception');
		} catch (CM_Exception_ActionLimit $e) {
			$this->assertTrue(true);
		}
	}
}

class CM_Action_Mock extends CM_Action_Abstract {
	public function getEntityType() {
		return 1;
	}

	protected function _notify(CM_Model_Entity_Abstract $entity, array $data = null) {
	}

	protected function _prepare() {
	}
}
