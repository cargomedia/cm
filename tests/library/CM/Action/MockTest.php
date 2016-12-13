<?php

class CM_Action_MockTest extends CMTest_TestCase {

    public function setUp() {
        CM_Config::get()->CM_Action_Abstract->verbs['foo'] = 1;
        CM_Config::get()->CM_Model_ActionLimit_Abstract->types[CM_Model_ActionLimit_Mock::getTypeStatic()] = 'CM_Model_ActionLimit_Mock';
    }

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testGetName() {
        $actor = CMTest_TH::createUser();
        $action = new CM_Action_Mock('foo', $actor);
        $this->assertSame('Mock', $action->getName());
    }

    public function testGetLabel() {
        $actor = CMTest_TH::createUser();
        $action = new CM_Action_Mock('foo', $actor);
        $this->assertSame('Mock Test', $action->getLabel());
    }

    public function testPrepare() {
        $actor = CMTest_TH::createUser();
        $action = new CM_Action_Mock('foo', $actor);
        $action->prepare(null);

        CM_Db_Db::insert('cm_actionLimit', array('type' => 1, 'actionType' => 1, 'actionVerb' => 1, 'role' => null, 'limit' => 0, 'period' => 0));
        CMTest_TH::clearCache();
        try {
            $action->prepare(null);
            $this->fail('Limited action did not throw exception');
        } catch (CM_Exception_ActionLimit $e) {
            $this->assertSame('Mock overshoot', $e->getMessage());
        }
    }

    /**
     * @expectedException CM_Exception_NotAllowed
     * @expectedExceptionMessage Action not allowed
     */
    public function testPrepareNotAllowed() {
        $actor = CMTest_TH::createUser();
        $action = new CM_Action_Mock('foo', $actor);
        $action->prepare(false);
    }
}

class CM_Action_Mock extends CM_Action_Abstract {

    protected function _notify() {
    }

    /**
     * @param bool $isAllowed
     * @return bool
     */
    protected function _isAllowedTest($isAllowed) {
        return $isAllowed;
    }

    protected function _prepare() {
    }

    public function getVerbName() {
        return 'Test';
    }

    public static function getTypeStatic() {
        return 1;
    }
}

class CM_Model_ActionLimit_Mock extends CM_Model_ActionLimit_Abstract {

    public function getOvershootAllowed(CM_Action_Abstract $action) {
        return false;
    }

    public function overshoot(CM_Action_Abstract $action, $role, $first) {
        throw new CM_Exception_ActionLimit('Mock overshoot');
    }

    public static function getTypeStatic() {
        return 1;
    }
}
