<?php

class CM_Clockwork_ManagerTest extends CMTest_TestCase {

  public function testRunEventsFor() {
    $manager = new CM_Clockwork_Manager();
    $counter = array(
      '1'  => 0,
      '2'  => 0,
      '5'  => 0,
      '60' => 0,
    );
    $currently = new DateTime();
    $this->_createEvent($manager, $currently, new DateInterval('PT1S'), $counter);
    $this->_createEvent($manager, $currently, new DateInterval('PT2S'), $counter);
    $this->_createEvent($manager, $currently, new DateInterval('PT5S'), $counter);
    $this->_createEvent($manager, $currently, new DateInterval('PT60S'), $counter);

    for ($i = 0; $i < 100; $i++) {
      $currently->add(new DateInterval('PT1S'));
      $manager->runEvents();
    }
    $this->assertSame(array(
      '1'  => 100,
      '2'  => 50,
      '5'  => 20,
      '60' => 2,
    ), $counter);
  }

  /**
   * @param CM_Clockwork_Manager $manager
   * @param DateTime             $timeReference
   * @param DateInterval         $interval
   * @param array                $counter
   */
  private function _createEvent(CM_Clockwork_Manager $manager, DateTime $timeReference, DateInterval $interval, &$counter) {
    $callback = function () use (&$counter, $interval) {
      $key = $interval->s;
      $counter[$key]++;
    };
    $event = $this->getMockBuilder('CM_Clockwork_Event')->setMethods(array('_getCurrentDateTime'))->setConstructorArgs(array($interval))->getMock();
    $event->expects($this->any())->method('_getCurrentDateTime')->will($this->returnCallback(function () use ($timeReference) {
      return clone $timeReference;
    }));
    /** @var CM_Clockwork_Event $event */
    $event->registerCallback($callback);
    $manager->registerEvent($event);
  }
}
