<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_Action_ActionTest extends TestCase {
	private static $_configBackup;

	public static function setUpBeforeClass() {
		self::$_configBackup = CM_Config::get();
		CM_Config::get()->CM_Model_ActionLimit_Abstract->types[CM_Model_ActionLimit_Mock::TYPE] = 'CM_Model_ActionLimit_Mock';
	}

	public static function tearDownAfterClass() {
		CM_Config::set(self::$_configBackup);
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
		$action = new CM_Action_Mock(1, $actor);
		$action->prepare();

		CM_Mysql::insert(TBL_CM_ACTIONLIMIT, array('type' => 1, 'modelType' => 1, 'actionType' => 1, 'role' => null, 'limit' => 0, 'period' => 0));
		TH::clearCache();
		try {
			$action->prepare();
			$this->fail('Limited action did not throw exception');
		} catch (CM_Exception_ActionLimit $e) {
			$this->assertSame('Mock overshoot', $e->getMessage());
		}
	}
}

class CM_Action_Mock extends CM_Action_Abstract {
	public function getModelType() {
		return 1;
	}

	protected function _notify(CM_Model_Abstract $model, array $data = null) {
	}

	protected function _prepare() {
	}
}

class CM_Model_ActionLimit_Mock extends CM_Model_ActionLimit_Abstract {
	const TYPE = 1;

	public function overshoot(CM_Action_Abstract $action, $role, $first) {
		throw new CM_Exception_ActionLimit('Mock overshoot');
	}
}
