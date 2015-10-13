<?php

class CM_Action_AbstractTest extends CMTest_TestCase {

    public function setUp() {
        CM_Config::get()->CM_Action_Abstract->verbs['foo'] = 1;
    }

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testConstruct() {
        $actor = CMTest_TH::createUser();
        /** @var CM_Action_Abstract|PHPUnit_Framework_MockObject_MockObject $action */
        $action = $this->getMockForAbstractClass('CM_Action_Abstract', array('foo', $actor), '', true, true, true, array('getType'));
        $action->expects($this->any())->method('getType')->will($this->returnValue(123));

        $this->assertInstanceOf('CM_Action_Abstract', $action);
        $this->assertSame(1, $action->getVerb());
        $this->assertSame($actor, $action->getActor());
        $this->assertNull($action->getIp());

        $actor = 123456; // IP address
        /** @var CM_Action_Abstract|PHPUnit_Framework_MockObject_MockObject $action */
        $action = $this->getMockForAbstractClass('CM_Action_Abstract', array('foo', $actor), '', true, true, true, array('getType'));
        $action->expects($this->any())->method('getType')->will($this->returnValue(123));
        $this->assertInstanceOf('CM_Action_Abstract', $action);
        $this->assertSame(1, $action->getVerb());
        $this->assertNull($action->getActor());
        $this->assertSame($actor, $action->getIp());

        try {
            $this->getMockForAbstractClass('CM_Action_Abstract', array('foo', 'bar'));
            $this->fail("Can instantiate action with actor `bar`");
        } catch (CM_Exception_Invalid $e) {
            $this->assertTrue(true);
        }

        try {
            $this->getMockForAbstractClass('CM_Action_Abstract', array('foo', null));
            $this->fail("Can instantiate action with actor `null`");
        } catch (CM_Exception_Invalid $e) {
            $this->assertTrue(true);
        }
    }

    public function testIsAllowed() {
        /** @var CM_Action_Abstract|\Mocka\AbstractClassTrait $action */
        $action = $this->mockObject('CM_Action_Abstract', ['foo', CMTest_TH::createUser()]);
        $action->mockMethod('getType')->set(12);
        $action->mockMethod('_isAllowedFoo')->set(function ($arg1, $config) {
            $this->assertSame('myArg', $arg1);
            return $config['allowed'];
        });

        $this->assertSame(true, $action->isAllowed('myArg', ['allowed' => true]));
        $this->assertSame(false, $action->isAllowed('myArg', ['allowed' => false]));
        $this->assertSame(true, $action->isAllowed('myArg', ['allowed' => null]));

        $action->mockMethod('getActionLimitsTransgressed')->set(function () {
            return [['actionLimit' => $this->_getActionLimitForbidden(), 'role' => null]];
        });

        $this->assertSame(true, $action->isAllowed('myArg', ['allowed' => true]));
        $this->assertSame(false, $action->isAllowed('myArg', ['allowed' => false]));
        $this->assertSame(false, $action->isAllowed('myArg', ['allowed' => null]));
    }

    public function testPrepareAllowed() {
        /** @var CM_Action_Abstract|\Mocka\AbstractClassTrait $action */
        $action = $this->mockObject('CM_Action_Abstract', ['foo', CMTest_TH::createUser()]);
        $action->mockMethod('getType')->set(12);
        $isAllowed = $action->mockMethod('_isAllowed')->set(function($arg1) {
            $this->assertSame('myArg', $arg1);
            return true;
        });
        $action->mockMethod('getActionLimitsTransgressed')->set(function () {
            return [['actionLimit' => $this->_getActionLimitForbidden(), 'role' => null]];
        });
        $prepare = $action->mockMethod('_prepare');

        $action->prepare('myArg');
        $this->assertSame(1, $isAllowed->getCallCount());
        $this->assertSame(1, $prepare->getCallCount());
    }

    public function testPrepareDisallowed() {
        /** @var CM_Action_Abstract|\Mocka\AbstractClassTrait $action */
        $action = $this->mockObject('CM_Action_Abstract', ['foo', CMTest_TH::createUser()]);
        $action->mockMethod('getType')->set(12);
        $isAllowedOrDisallowed = $action->mockMethod('_isAllowed')->set(function($arg1) {
            $this->assertSame('myArg', $arg1);
            return false;
        });
        $action->mockMethod('getActionLimitsTransgressed')->set(function () {
            return [['actionLimit' => $this->_getActionLimitForbidden(), 'role' => null]];
        });
        $prepare = $action->mockMethod('_prepare');

        try {
            $action->prepare('myArg');
            $this->fail('Prepare should throw');
        } catch(CM_Exception_NotAllowed $e) {
            $this->assertSame('Action not allowed', $e->getMessage());
        }
        $this->assertSame(1, $isAllowedOrDisallowed->getCallCount());
        $this->assertSame(0, $prepare->getCallCount());
    }

    public function testPrepareActionLimit() {
        /** @var CM_Action_Abstract|\Mocka\AbstractClassTrait $action */
        $action = $this->mockObject('CM_Action_Abstract', ['foo', CMTest_TH::createUser()]);
        $action->mockMethod('getType')->set(12);
        $isAllowedOrDisallowed = $action->mockMethod('_isAllowed')->set(function($arg1) {
            $this->assertSame('myArg', $arg1);
            return null;
        });
        $action->mockMethod('getActionLimitsTransgressed')->set(function () {
            return [['actionLimit' => $this->_getActionLimitForbidden(), 'role' => null]];
        });
        $prepare = $action->mockMethod('_prepare');

        try {
            $action->prepare('myArg');
            $this->fail('Prepare should throw');
        } catch(Exception $e) {
            $this->assertSame('my overshoot', $e->getMessage());
        }
        $this->assertSame(1, $isAllowedOrDisallowed->getCallCount());
        $this->assertSame(0, $prepare->getCallCount());
    }

    public function testPrepareUser() {
        $user = CMTest_TH::createUser();
        $hardLimit = $this->getMockBuilder('CM_Model_ActionLimit_Abstract')->disableOriginalConstructor()
            ->setMethods(array('getType', 'getLimit', 'getPeriod', 'getOvershootAllowed', 'overshoot'))->getMockForAbstractClass();
        $hardLimit->expects($this->any())->method('getType')->will($this->returnValue(1));
        $hardLimit->expects($this->any())->method('getLimit')->will($this->returnValue(12));
        $hardLimit->expects($this->any())->method('getPeriod')->will($this->returnValue(60));
        $hardLimit->expects($this->any())->method('getOvershootAllowed')->will($this->returnValue(false));
        /** @var CM_Model_ActionLimit_Abstract $hardLimit */
        $softLimit = $this->getMockBuilder('CM_Model_ActionLimit_Abstract')->disableOriginalConstructor()
            ->setMethods(array('getType', 'getLimit', 'getPeriod', 'getOvershootAllowed', 'overshoot'))->getMockForAbstractClass();
        $softLimit->expects($this->any())->method('getType')->will($this->returnValue(2));
        $softLimit->expects($this->any())->method('getLimit')->will($this->returnValue(3));
        $softLimit->expects($this->any())->method('getPeriod')->will($this->returnValue(10));
        $softLimit->expects($this->any())->method('getOvershootAllowed')->will($this->returnValue(true));
        /** @var CM_Model_ActionLimit_Abstract $softLimit */

        $actionLimitPaging = $this->getMockBuilder('CM_Paging_Abstract')
            ->setConstructorArgs(array(new CM_PagingSource_Array(array($softLimit, $hardLimit))))->getMockForAbstractClass();

        $action = $this->getMockBuilder('CM_Action_Abstract')->setConstructorArgs(array(CM_Action_Abstract::CREATE, $user))
            ->setMethods(array('_getActionLimitList', 'getType'))->getMockForAbstractClass();
        $action->expects($this->any())->method('getType')->will($this->returnValue(999));
        $action->expects($this->any())->method('_getActionLimitList')->will($this->returnValue($actionLimitPaging));
        /** @var CM_Action_Abstract $action */

        CMTest_TH::timeForward(2);
        $action->prepare();
        $action->prepare();
        $action->prepare();
        CMTest_TH::timeForward(8);
        // transgression
        $action->prepare();
        CMTest_TH::timeForward(1);
        // actions within first period, no new transgressions
        $action->prepare();
        $action->prepare();
        $action->prepare();
        CMTest_TH::timeForward(1);
        // first period over, new transgression
        $action->prepare();
        CMTest_TH::timeForward(10);
        $action->prepare();
        $action->prepare();
        CMTest_TH::timeForward(10);
        $action->prepare();
        $action->prepare();
        try {
            $action->prepare();
            $this->fail('Action breached hard limit.');
        } catch (CM_Exception_NotAllowed $ex) {
            $this->assertContains('ActionLimit `' . $hardLimit->getType() . '` breached', $ex->getMessage());
        }
        // hard limit reached, transgression not logged
        try {
            $action->prepare();
            $this->fail('Action breached hard limit.');
        } catch (CM_Exception_NotAllowed $ex) {
            $this->assertContains('ActionLimit `' . $hardLimit->getType() . '` breached', $ex->getMessage());
        }
        CMTest_TH::timeForward(30);
        $action->prepare();
        $action->prepare();
        $action->prepare();
        $this->assertSame(15, $user->getActions()->getCount());
        $this->assertSame(2, $user->getTransgressions(null, null, $softLimit->getType())->getCount());
        $this->assertSame(1, $user->getTransgressions(null, null, $hardLimit->getType())->getCount());
    }

    public function testPrepareIP() {
        $ip = 1237865;
        $hardLimit = $this->getMockBuilder('CM_Model_ActionLimit_Abstract')->disableOriginalConstructor()
            ->setMethods(array('getType', 'getLimit', 'getPeriod', 'getOvershootAllowed', 'overshoot'))->getMockForAbstractClass();
        $hardLimit->expects($this->any())->method('getType')->will($this->returnValue(1));
        $hardLimit->expects($this->any())->method('getLimit')->will($this->returnValue(3));
        $hardLimit->expects($this->any())->method('getPeriod')->will($this->returnValue(10));
        $hardLimit->expects($this->any())->method('getOvershootAllowed')->will($this->returnValue(false));
        /** @var CM_Model_ActionLimit_Abstract $hardLimit */

        $actionLimitPaging = $this->getMockBuilder('CM_Paging_Abstract')->setConstructorArgs(array(new CM_PagingSource_Array(array($hardLimit))))->getMockForAbstractClass();

        $action = $this->getMockBuilder('CM_Action_Abstract')->setConstructorArgs(array(CM_Action_Abstract::CREATE, $ip))
            ->setMethods(array('_getActionLimitList', 'getType'))->getMockForAbstractClass();
        $action->expects($this->any())->method('getType')->will($this->returnValue(999));
        $action->expects($this->any())->method('_getActionLimitList')->will($this->returnValue($actionLimitPaging));
        /** @var CM_Action_Abstract $action */

        $action->prepare();
        $action->prepare();
        $action->prepare();
        try {
            $action->prepare();
            $this->fail('Action breached hard limit.');
        } catch (CM_Exception_NotAllowed $ex) {
            $this->assertContains('ActionLimit `' . $hardLimit->getType() . '` breached', $ex->getMessage());
        }
        try {
            $action->prepare();
            $this->fail('Action breached hard limit.');
        } catch (CM_Exception_NotAllowed $ex) {
            $this->assertContains('ActionLimit `' . $hardLimit->getType() . '` breached', $ex->getMessage());
        }
        CMTest_TH::timeForward(10);
        $action->prepare();
        $actionList = new CM_Paging_Action_Ip($ip, $action->getType(), $action->getVerb(), null, null);
        $transgressionList = new CM_Paging_Transgression_Ip($ip, $action->getType(), $action->getVerb(), $hardLimit->getType());
        $this->assertSame(4, $actionList->getCount());
        $this->assertSame(1, $transgressionList->getCount());
    }

    public function testAggregate() {
        CMTest_TH::timeForward(-(time() % 30)); // Make sure time() is a multiple of 30

        $time = time() - 86400;
        $values = array();
        $values[] = array(1, null, 1, 1, null, time() - 10000, 1);
        $values[] = array(1, null, 1, 1, null, $time - 1, 2);
        $values[] = array(1, null, 1, 1, null, $time - 2, 1);
        $values[] = array(1, null, 1, 1, null, $time - 5, 1);
        $values[] = array(1, null, 1, 1, null, $time - 6, 1);
        $values[] = array(1, null, 1, 1, null, $time - 7, 1);
        $values[] = array(1, null, 1, 1, null, $time - 8, 1);
        $values[] = array(1, null, 1, 1, null, $time - 9, 1);
        $values[] = array(1, null, 1, 1, null, $time - 10, 4);
        $values[] = array(null, 1, 1, 1, null, $time - 11, 1);
        $values[] = array(null, 1, 1, 1, null, $time - 14, 1);
        $values[] = array(null, 1, 1, 1, null, $time - 15, 1);
        $values[] = array(null, 1, 1, 1, null, $time - 18, 1);
        $values[] = array(null, 1, 1, 1, null, $time - 20, 1);
        $values[] = array(null, 1, 1, 1, null, $time - 21, 1);
        $values[] = array(null, 1, 1, 1, null, $time - 25, 1);
        $values[] = array(null, 1, 1, 1, null, $time - 27, 1);
        $values[] = array(null, 1, 1, 1, null, $time - 30, 1);
        $values[] = array(null, 1, 1, 1, null, $time - 40, 1);
        $values[] = array(null, 1, 1, 1, null, $time - 50, 1);
        $values[] = array(null, 1, 1, 1, null, $time - 60, 10);
        $values[] = array(null, 1, 2, 1, null, $time - 9, 1);
        $values[] = array(null, 1, 2, 1, null, $time - 9, 1);
        $values[] = array(null, 1, 2, 1, null, $time - 10, 2);
        $values[] = array(null, 1, 2, 1, null, $time - 11, 1);
        $values[] = array(null, 1, 2, 1, null, $time - 12, 1);
        $values[] = array(null, 1, 2, 1, null, $time - 13, 1);
        $values[] = array(null, 1, 2, 1, null, $time - 14, 1);
        $values[] = array(null, 1, 2, 1, null, $time - 15, 1);
        $values[] = array(null, 1, 2, 1, null, $time - 16, 1);
        $values[] = array(null, 1, 2, 1, 1, $time - 6, 1);
        $values[] = array(null, 1, 2, 1, 1, $time - 6, 2);
        $values[] = array(null, 1, 2, 1, 2, $time - 6, 3);
        $values[] = array(null, 1, 2, 1, 2, $time - 7, 4);
        $values[] = array(null, 1, 2, 1, 2, $time - 1, 5);
        $values[] = array(null, 1, 2, 1, 2, $time - 1, 6);
        $values[] = array(null, 1, 1, 2, null, $time - 17, 1);
        $values[] = array(null, 1, 1, 2, null, $time - 18, 1);
        $values[] = array(null, 1, 1, 2, null, $time - 19, 1);
        $values[] = array(null, 1, 1, 2, null, $time - 20, 1);
        $values[] = array(null, 1, 1, 2, null, $time - 21, 1);
        $values[] = array(null, 1, 1, 2, null, $time - 22, 1);
        $values[] = array(null, 1, 1, 2, null, $time - 23, 1);
        $values[] = array(null, 1, 1, 2, null, $time - 24, 4);
        CM_Db_Db::insert('cm_action', array('actorId', 'ip', 'verb', 'type', 'actionLimitType', 'createStamp', 'count'), $values);
        CM_Action_Abstract::aggregate(array(array('interval' => 5, 'limit' => 86400), array('interval' => 10, 'limit' => 86400 + 20),
            array('interval' => 30, 'limit' => 86400 + 30)));
        $this->assertRow('cm_action', array('verb' => 1, 'type' => 1, 'interval' => 1, 'count' => 1));
        $this->assertRow('cm_action', array('verb' => 1, 'type' => 1, 'interval' => 5, 'count' => 4));
        $this->assertRow('cm_action', array('verb' => 1, 'type' => 1, 'interval' => 5, 'count' => 8));
        $this->assertRow('cm_action', array('verb' => 1, 'type' => 1, 'interval' => 5, 'count' => 3));
        $this->assertRow('cm_action', array('verb' => 1, 'type' => 1, 'interval' => 5, 'count' => 2));
        $this->assertRow('cm_action', array('verb' => 1, 'type' => 1, 'interval' => 10, 'count' => 4));
        $this->assertRow('cm_action', array('verb' => 1, 'type' => 1, 'interval' => 30, 'count' => 12));
        $this->assertRow('cm_action', array('verb' => 2, 'type' => 1, 'interval' => 5, 'count' => 4));
        $this->assertRow('cm_action', array('verb' => 2, 'type' => 1, 'interval' => 5, 'count' => 5));
        $this->assertRow('cm_action', array('verb' => 2, 'type' => 1, 'interval' => 5, 'count' => 1));
        $this->assertRow('cm_action', array('verb' => 2, 'type' => 1, 'interval' => 1, 'count' => 1, 'actionLimitType' => 1));
        $this->assertRow('cm_action', array('verb' => 2, 'type' => 1, 'interval' => 1, 'count' => 2, 'actionLimitType' => 1));
        $this->assertRow('cm_action', array('verb' => 2, 'type' => 1, 'interval' => 1, 'count' => 3, 'actionLimitType' => 2));
        $this->assertRow('cm_action', array('verb' => 2, 'type' => 1, 'interval' => 1, 'count' => 4, 'actionLimitType' => 2));
        $this->assertRow('cm_action', array('verb' => 2, 'type' => 1, 'interval' => 1, 'count' => 5, 'actionLimitType' => 2));
        $this->assertRow('cm_action', array('verb' => 2, 'type' => 1, 'interval' => 1, 'count' => 6, 'actionLimitType' => 2));
        $this->assertRow('cm_action', array('verb' => 1, 'type' => 2, 'interval' => 5, 'count' => 4));
        $this->assertRow('cm_action', array('verb' => 1, 'type' => 2, 'interval' => 10, 'count' => 7));

        $this->assertEquals(18, CM_Db_Db::count('cm_action'));
    }

    public function testAggregateInvalidIntervals() {
        try {
            CM_Action_Abstract::aggregate(array(array('interval' => 5, 'limit' => 10), array('interval' => 11, 'limit' => 20)));
            $this->fail('Invalid intervals were not detected');
        } catch (CM_Exception_Invalid $e) {
            $this->assertContains('`11` is not a multiple of `5`', $e->getMessage());
        }

        try {
            CM_Action_Abstract::aggregate(array(array('interval' => 5, 'limit' => 10), array('interval' => 10, 'limit' => 20),
                array('interval' => 21, 'limit' => 30)));
            $this->fail('Invalid intervals were not detected');
        } catch (CM_Exception_Invalid $e) {
            $this->assertContains('`21` is not a multiple of `10`', $e->getMessage());
        }
    }

    public function testCollapse() {
        $values[] = array(1, null, 1, 1, null, 1, 1);
        $values[] = array(1, null, 1, 1, null, 1, 2);
        $values[] = array(1, null, 1, 1, null, 2, 1);
        $values[] = array(1, null, 1, 1, null, 3, 1);
        $values[] = array(1, null, 1, 1, null, 4, 10);
        $values[] = array(1, null, 1, 1, 1, 4, 10);
        $values[] = array(1, null, 2, 1, null, 4, 100);
        $values[] = array(1, null, 1, 2, null, 4, 100);
        $values[] = array(1, null, 1, 1, null, 5, 100);
        CM_Db_Db::insert('cm_action', array('actorId', 'ip', 'verb', 'type', 'actionLimitType', 'createStamp', 'count'), $values);
        CM_Action_Abstract::collapse(1, 4);
        $this->assertEquals(6, CM_Db_Db::count('cm_action'));
        $this->assertRow('cm_action', array('verb' => 1, 'type' => 1, 'createStamp' => 2, 'count' => 5));
    }

    public function testNotify() {
        $actor = CMTest_TH::createUser();
        $action = $this->getMockBuilder('CM_Action_Abstract')->setMethods(array('_notifyFoo', '_track'))
            ->setConstructorArgs(array('foo', $actor))->getMockForAbstractClass();
        $action->expects($this->once())->method('_notifyFoo')->with('bar');

        $method = CMTest_TH::getProtectedMethod('CM_Action_Abstract', '_notify');
        $method->invoke($action, 'bar');
    }

    public function testGetLabel() {
        $userMock = $this->getMock('CM_Model_User');
        $argumentList = array(CM_Action_Abstract::VIEW, $userMock);
        $actionMock = $this->getMockForAbstractClass('CM_Action_Abstract', $argumentList, 'CM_Action_EmailNotification_Reminder');
        /** @var CM_Action_Abstract $actionMock */
        $this->assertSame('Email Notification Reminder View', $actionMock->getLabel());
    }

    public function testDeleteTransgressionsOlder() {
        $user = CMTest_TH::createUser();
        $action = $this->mockClass('CM_Action_Abstract')->newInstanceWithoutConstructor();
        $action->mockMethod('getType')->set(function () {
            return 1;
        });
        $action->mockMethod('getVerb')->set(function () {
            return 2;
        });
        /** @var CM_Action_Abstract $action */

        $transgressions = $user->getTransgressions();
        $actions = $user->getActions();

        $transgressions->add($action, 1);
        $transgressions->add($action, 2);
        $actions->add($action, 1);

        CMTest_TH::timeForward(61);
        CM_Action_Abstract::deleteTransgressionsOlder(60);

        $transgressions->add($action, 3);

        $this->assertCount(1, $actions);
        $this->assertCount(1, $transgressions);

        CMTest_TH::timeForward(61);
        CM_Action_Abstract::deleteTransgressionsOlder(60);
        $transgressions->_change();
        $this->assertCount(0, $transgressions);
    }

    /**
     * @return CM_Model_ActionLimit_Abstract
     */
    private function _getActionLimitForbidden() {
        /** @var CM_Model_ActionLimit_Abstract|\Mocka\AbstractClassTrait $actionLimitForbidden */
        $actionLimit = $this->mockClass('CM_Model_ActionLimit_Abstract')->newInstanceWithoutConstructor();
        $actionLimit->mockMethod('getType')->set(1);
        $actionLimit->mockMethod('getLimit')->set(12);
        $actionLimit->mockMethod('getPeriod')->set(60);
        $actionLimit->mockMethod('getOvershootAllowed')->set(false);
        $actionLimit->mockMethod('overshoot')->set(function () {
            throw new Exception('my overshoot');
        });
        return $actionLimit;
    }
}
