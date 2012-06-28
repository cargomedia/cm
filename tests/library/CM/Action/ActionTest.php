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
		$action = $this->getMockForAbstractClass('CM_Action_Abstract', array(1, $actor), '', true, true, true, array('getType'));
		$action->expects($this->any())->method('getType')->will($this->returnValue(123));

		$this->assertInstanceOf('CM_Action_Abstract', $action);
		$this->assertSame(1, $action->getVerb());
		$this->assertSame($actor, $action->getActor());
		$this->assertNull($action->getIp());

		$actor = 123456; // IP address
		/** @var CM_Action_Abstract $action */
		$action = $this->getMockForAbstractClass('CM_Action_Abstract', array(1, $actor), '', true, true, true, array('getType'));
		$action->expects($this->any())->method('getType')->will($this->returnValue(123));
		$this->assertInstanceOf('CM_Action_Abstract', $action);
		$this->assertSame(1, $action->getVerb());
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

		CM_Mysql::insert(TBL_CM_ACTIONLIMIT, array('type' => 1, 'actionType' => 1, 'actionVerb' => 1, 'role' => null, 'limit' => 0, 'period' => 0));
		TH::clearCache();
		try {
			$action->prepare();
			$this->fail('Limited action did not throw exception');
		} catch (CM_Exception_ActionLimit $e) {
			$this->assertSame('Mock overshoot', $e->getMessage());
		}
	}

	public function testAggregate() {
		TH::timeForward(-(time() % 30)); // Make sure time() is a multiple of 30

		$time = time() - 86400;
		CM_Mysql::insert(TBL_CM_ACTION, array('actorId', 'ip', 'verb', 'type', 'actionLimitType', 'createStamp', 'count'),
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
				array(null, 1, 2, 1, 1, $time-6, 1),
				array(null, 1, 2, 1, 1, $time-6, 2),
				array(null, 1, 2, 1, 2, $time-6, 3),
				array(null, 1, 2, 1, 2, $time-7, 4),
				array(null, 1, 2, 1, 2, $time-1, 5),
				array(null, 1, 2, 1, 2, $time-1, 6),
				array(null, 1, 1, 2, null, $time-17, 1),
				array(null, 1, 1, 2, null, $time-18, 1),
				array(null, 1, 1, 2, null, $time-19, 1),
				array(null, 1, 1, 2, null, $time-20, 1),
				array(null, 1, 1, 2, null, $time-21, 1),
				array(null, 1, 1, 2, null, $time-22, 1),
				array(null, 1, 1, 2, null, $time-23, 1),
				array(null, 1, 1, 2, null, $time-24, 4),
		));
		CM_Action_Abstract::aggregate(array(array('interval' => 5, 'limit' => 86400), array('interval' =>10, 'limit' => 86400+20), array('interval' => 30, 'limit' => 86400+30)));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 1, 'type' => 1, 'interval' => 1, 'count' => 1));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 1, 'type' => 1, 'interval' => 5, 'count' => 4));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 1, 'type' => 1, 'interval' => 5, 'count' => 8));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 1, 'type' => 1, 'interval' => 5, 'count' => 3));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 1, 'type' => 1, 'interval' => 5, 'count' => 2));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 1, 'type' => 1, 'interval' => 10, 'count' => 4));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 1, 'type' => 1, 'interval' => 30, 'count' => 12));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 2, 'type' => 1, 'interval' => 5, 'count' => 4));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 2, 'type' => 1, 'interval' => 5, 'count' => 5));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 2, 'type' => 1, 'interval' => 5, 'count' => 1));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 2, 'type' => 1, 'interval' => 1, 'count' => 1, 'actionLimitType' => 1));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 2, 'type' => 1, 'interval' => 1, 'count' => 2, 'actionLimitType' => 1));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 2, 'type' => 1, 'interval' => 1, 'count' => 3, 'actionLimitType' => 2));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 2, 'type' => 1, 'interval' => 1, 'count' => 4, 'actionLimitType' => 2));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 2, 'type' => 1, 'interval' => 1, 'count' => 5, 'actionLimitType' => 2));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 2, 'type' => 1, 'interval' => 1, 'count' => 6, 'actionLimitType' => 2));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 1, 'type' => 2, 'interval' => 5, 'count' => 4));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 1, 'type' => 2, 'interval' => 10, 'count' => 7));

		$this->assertEquals(18, CM_Mysql::count(TBL_CM_ACTION));
	}

	public function testAggregateInvalidIntervals() {
		try {
			CM_Action_Abstract::aggregate(array(array('interval' => 5, 'limit' => 10), array('interval' =>11, 'limit' => 20)));
			$this->fail('Invalid intervals were not detected');
		} catch(CM_Exception_Invalid $e) {
			$this->assertContains('`11` is not a multiple of `5`', $e->getMessage());
		}

		try {
			CM_Action_Abstract::aggregate(array(array('interval' => 5, 'limit' => 10), array('interval' =>10, 'limit' => 20), array('interval' =>21, 'limit' => 30)));
			$this->fail('Invalid intervals were not detected');
		} catch(CM_Exception_Invalid $e) {
			$this->assertContains('`21` is not a multiple of `10`', $e->getMessage());
		}
	}

	public function testCollapse() {
		CM_Mysql::insert(TBL_CM_ACTION, array('actorId', 'ip', 'verb', 'type', 'actionLimitType', 'createStamp', 'count'),
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
		CM_Action_Abstract::collapse(1, 4);
		$this->assertEquals(6, CM_Mysql::count(TBL_CM_ACTION));
		$this->assertRow(TBL_CM_ACTION, array('verb' => 1, 'type' => 1, 'createStamp' => 2, 'count' => 5));
	}

	public function testForceAllow() {
		$actor = TH::createUser();
		$action = new CM_Action_Mock(1, $actor);

		CM_Mysql::insert(TBL_CM_ACTIONLIMIT, array('type' => 1, 'actionType' => 1, 'actionVerb' => 1, 'role' => null, 'limit' => 0, 'period' => 0));

		$action->forceAllow(false);
		try {
			$action->prepare();
			$this->fail('Limited action did not throw exception');
		} catch (CM_Exception_ActionLimit $e) {
			$this->assertSame('Mock overshoot', $e->getMessage());
		}

		$action->forceAllow(true);
		$action->prepare();
		$this->assertTrue(true);
	}
}

class CM_Action_Mock extends CM_Action_Abstract {
	const TYPE = 1;

	protected function _notify() {
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
