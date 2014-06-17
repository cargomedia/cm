<?php

class CM_Clockwork_PersistenceTest extends CMTest_TestCase {

    public function testGetLastRunTime() {
        $interval = new DateInterval('P1D');
        $event1 = new CM_Clockwork_Event('event1', $interval);
        $event2 = new CM_Clockwork_Event('event2', $interval);
        $event3 = new CM_Clockwork_Event('event3', $interval);

        $runTime1 = new DateTime('@' . 123);
        $runTime2 = new DateTime('@' . 321);

        $context = 'foo';
        $adapter = $this->getMockBuilder('CM_Clockwork_PersistenceAdapter_Abstract')->setMethods(array('load'))
            ->getMockForAbstractClass();
        $adapter->expects($this->any())->method('load')->with($context)->will($this->returnValue(array($event1->getName() => $runTime1, $event2->getName() => $runTime2)));
        $persistence = new CM_Clockwork_Persistence($context, $adapter);

        $this->assertEquals($runTime1, $persistence->getLastRunTime($event1));
        $this->assertEquals($runTime2, $persistence->getLastRunTime($event2));
        $this->assertNull($persistence->getLastRunTime($event3));
    }

    public function testSetRuntime() {
        $interval = new DateInterval('P1D');
        $event1 = new CM_Clockwork_Event('event1', $interval);
        $event2 = new CM_Clockwork_Event('event2', $interval);

        $runTime1 = new DateTime('@' . 123);
        $runTime2 = new DateTime('@' . 321);

        $context = 'foo';
        $adapter = $this->getMockBuilder('CM_Clockwork_PersistenceAdapter_Abstract')->setMethods(array('save', 'load'))
            ->getMockForAbstractClass();
        $adapter->expects($this->any())->method('load')->with($context)->will($this->returnValue(array()));
        $adapter->expects($this->at(1))->method('save')->with($context, array($event1->getName() => $runTime1));
        $adapter->expects($this->at(2))->method('save')->with($context, array($event1->getName() => $runTime1, $event2->getName() => $runTime2));
        $persistence = new CM_Clockwork_Persistence($context, $adapter);

        $persistence->setRuntime($event1, $runTime1);
        $persistence->setRuntime($event2, $runTime2);
    }
}
