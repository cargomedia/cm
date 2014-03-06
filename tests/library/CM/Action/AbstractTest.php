<?php

class CM_Action_AbstractTest extends CMTest_TestCase {

  public function setUp() {
      if (getenv('TRAVIS') && (PHP_MAJOR_VERSION === 5) && (PHP_MINOR_VERSION === 4)) {
          $this->markTestSkipped('Causes a Segfault on Travis after code coverage (PDO: Broken pipe)');
      }
    CM_Config::get()->CM_Action_Abstract->verbs['Foo'] = 1;
  }

  public function testNotify() {
    $actor = CMTest_TH::createUser();
    $action = $this->getMockBuilder('CM_Action_Abstract')->setMethods(array('_notifyFoo', '_track'))
      ->setConstructorArgs(array('Foo', $actor))->getMockForAbstractClass();
    // Cannot check due to https://github.com/sebastianbergmann/phpunit-mock-objects/issues/139
    // $action->expects($this->once())->method('_notifyFoo')->with('bar');
    $action->expects($this->once())->method('_track');

    $method = CMTest_TH::getProtectedMethod('CM_Action_Abstract', '_notify');
    $method->invoke($action, 'bar');
  }

  public function testTrack() {
    CM_Config::get()->CM_KissTracking->enabled = true;
    $tracking = CM_KissTracking::getInstance();
    $getEventsMethod = CMTest_TH::getProtectedMethod('CM_KissTracking', '_getEvents');

    $actor = CMTest_TH::createUser();
    $action = $this->getMockBuilder('CM_Action_Abstract')->setConstructorArgs(array('Foo', $actor))->getMockForAbstractClass();

    $getEventsMethod->invoke($tracking);
    $nofifyMethod = CMTest_TH::getProtectedMethod('CM_Action_Abstract', '_notify');
    $nofifyMethod->invoke($action, 'bar');
    $this->assertCount(1, $getEventsMethod->invoke($tracking));
  }

  public function testPrepareActionUser() {
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

  public function testPrepareActionIP() {
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
}
