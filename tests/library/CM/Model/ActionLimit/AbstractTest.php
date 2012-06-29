<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Model_ActionLimit_AbstractTest extends TestCase {

	private $_actionType = 1;
	private $_actionVerb = 2;
	private $_type = 3;
	private $_role = 1;

	public static function setUpBeforeClass() {

	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function setup() {
		TH::clearEnv();
		CM_Mysql::replace(TBL_CM_ACTIONLIMIT, array('actionType', 'actionVerb', 'type', 'role', 'limit', 'period'), array(array($this->_actionType,
			$this->_actionVerb, $this->_type, $this->_role, 2, 3), array($this->_actionType, $this->_actionVerb, $this->_type, null, 10, 11)));
	}

	public function testConstruct() {
		$actionLimit = new CM_Model_ActionLimit_AbstractMock($this->_actionType, $this->_actionVerb);
		$this->assertEquals(3, $actionLimit->getType(), 'type not mocked');
		$this->assertSame($this->_type, $actionLimit->getType());
		$this->assertSame($this->_actionType, $actionLimit->getActionType());
		$this->assertSame($this->_actionVerb, $actionLimit->getActionVerb());
		$this->assertEquals(2, $actionLimit->getLimit($this->_role));
		$this->assertEquals(3, $actionLimit->getPeriod($this->_role));
	}

	public function testSetLimit() {
		$actionLimit = new CM_Model_ActionLimit_AbstractMock($this->_actionType, $this->_actionVerb);
		$this->assertEquals(2, $actionLimit->getLimit($this->_role));
		$actionLimit->setLimit($this->_role, 3);
		$this->assertEquals(3, $actionLimit->getLimit($this->_role));
		$actionLimit->setLimit($this->_role, null);
		$this->assertNull($actionLimit->getLimit($this->_role));
	}

	public function testSetPeriod() {
		$actionLimit = new CM_Model_ActionLimit_AbstractMock($this->_actionType, $this->_actionVerb);
		$this->assertSame(3, $actionLimit->getPeriod($this->_role));
		$actionLimit->setPeriod($this->_role, 6);
		$this->assertSame(6, $actionLimit->getPeriod($this->_role));
		$actionLimit->setPeriod($this->_role, null);
		$this->assertSame(0, $actionLimit->getPeriod($this->_role));
	}

	public function testUnsetLimit() {
		$actionLimit = new CM_Model_ActionLimit_AbstractMock($this->_actionType, $this->_actionVerb);
		$this->assertEquals(2, $actionLimit->getLimit($this->_role));
		$this->assertSame(3, $actionLimit->getPeriod($this->_role));
		$actionLimit->unsetLimit($this->_role);
		$this->assertEquals(10, $actionLimit->getLimit($this->_role));
		$this->assertSame(11, $actionLimit->getPeriod($this->_role));
		$actionLimit->unsetLimit();
		$this->assertNull($actionLimit->getLimit($this->_role));
		$this->assertNull($actionLimit->getPeriod($this->_role));
	}
}

class CM_Model_ActionLimit_AbstractMock extends CM_Model_ActionLimit_Abstract {
	const TYPE = 3;

	public function overshoot(CM_Action_Abstract $action, $role, $first) {
		throw new CM_Exception_ActionLimit('Mock overshoot');
	}
}
