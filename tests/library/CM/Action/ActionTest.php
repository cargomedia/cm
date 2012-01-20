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

	public function tearDown() {
		CM_Mysql::truncate(TBL_CM_ACTION);
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

	public function testAggregate() {
		$time = time() - 86400;
		$time = $time - $time % 30;
		CM_Mysql::insert(TBL_CM_ACTION, array('actorId', 'ip', 'actionType', 'modelType', 'actionLimitType', 'createStamp', 'count'),
			array(
				array(1, null, 1, 1, null, time()-10000, 1),
				array(1, null, 1, 1, null, $time-1, 2),
				array(1, null, 1, 1, null, $time-2, 1),
				array(1, null, 1, 1, null, $time-5, 1),
				array(1, null, 1, 1, null, $time-6, 1),
				array(1, null, 1, 1, null, $time-7, 1),
				array(1, null, 1, 1, null, $time-8, 1),
				array(1, null, 1, 1, null, $time-9, 1),
				array(1, null, 1, 1, null, $time-10, 4),
				array(null, 1, 1, 1, null, $time-11, 1),
				array(null, 1, 1, 1, null, $time-14, 1),
				array(null, 1, 1, 1, null, $time-15, 1),
				array(null, 1, 1, 1, null, $time-18, 1),
				array(null, 1, 1, 1, null, $time-20, 1),
				array(null, 1, 1, 1, null, $time-21, 1),
				array(null, 1, 1, 1, null, $time-25, 1),
				array(null, 1, 1, 1, null, $time-27, 1),
				array(null, 1, 1, 1, null, $time-30, 1),
				array(null, 1, 1, 1, null, $time-40, 1),
				array(null, 1, 1, 1, null, $time-50, 1),
				array(null, 1, 1, 1, null, $time-60, 10),
				array(null, 1, 2, 1, null, $time-9, 1),
				array(null, 1, 2, 1, null, $time-9, 1),
				array(null, 1, 2, 1, null, $time-10, 2),
				array(null, 1, 2, 1, null, $time-11, 1),
				array(null, 1, 2, 1, null, $time-12, 1),
				array(null, 1, 2, 1, null, $time-13, 1),
				array(null, 1, 2, 1, null, $time-14, 1),
				array(null, 1, 2, 1, null, $time-15, 1),
				array(null, 1, 2, 1, null, $time-16, 1),
				array(null, 1, 2, 1, 1, $time-6, 2),
				array(null, 1, 2, 1, 1, $time-6, 2),
				array(null, 1, 2, 1, 2, $time-6, 2),
				array(null, 1, 2, 1, 2, $time-7, 2),
				array(null, 1, 2, 1, 2, $time-1, 1),
				array(null, 1, 2, 1, 2, $time-1, 1),
				array(null, 1, 1, 2, null, $time-17, 1),
				array(null, 1, 1, 2, null, $time-18, 1),
				array(null, 1, 1, 2, null, $time-19, 1),
				array(null, 1, 1, 2, null, $time-20, 1),
				array(null, 1, 1, 2, null, $time-21, 1),
				array(null, 1, 1, 2, null, $time-22, 1),
				array(null, 1, 1, 2, null, $time-23, 1),
				array(null, 1, 1, 2, null, $time-24, 4),
		));
		CM_Action_Abstract::aggregate(array(array('interval' => 5, 'limit' => 10), array('interval' =>10, 'limit' => 30), array('interval' => 30, 'limit' => $time - CM_Mysql::exec('SELECT MIN(`createStamp`) FROM ' . TBL_CM_ACTION)->fetchOne())));
		$this->assertEquals(18, CM_Mysql::count(TBL_CM_ACTION));
	}

	public function testCollapse() {
		CM_Mysql::insert(TBL_CM_ACTION, array('actorId', 'ip', 'actionType', 'modelType', 'actionLimitType', 'createStamp', 'count'),
			array(
				array(1, null, 1, 1, null, 1, 1),
				array(1, null, 1, 1, null, 1, 2),
				array(1, null, 1, 1, null, 2, 1),
				array(1, null, 1, 1, null, 3, 1),
				array(1, null, 1, 1, null, 4, 10),
				array(1, null, 1, 1, 1, 4, 10),
				array(1, null, 2, 1, null, 4, 100),
				array(1, null, 1, 2, null, 4, 100),
				array(1, null, 1, 1, null, 5, 100),
		));
		CM_Action_Abstract::collapse(1, 4, 1, 1);
		$this->assertEquals(7, CM_Mysql::count(TBL_CM_ACTION));
		$this->assertRow(TBL_CM_ACTION, array('actionType' => 1, 'modelType' => 1, 'createStamp' => 2, 'count' => 12));

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
