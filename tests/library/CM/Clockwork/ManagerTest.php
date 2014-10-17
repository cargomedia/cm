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
        $currently = $this->_getCurrentDateTime();
        $this->_createEvent($manager, $currently, '1 second', $counter, 'event1');
        $this->_createEvent($manager, $currently, '2 seconds', $counter, 'event2');
        $this->_createEvent($manager, $currently, '5 seconds', $counter, 'event3');
        $this->_createEvent($manager, $currently, '60 seconds', $counter, 'event4');

        for ($i = 0; $i <= 100; $i++) {
            $manager->runEvents();
            $currently->add(new DateInterval('PT1S'));
        }
        $this->assertSame(array(
            '1'  => 100,
            '2'  => 50,
            '5'  => 20,
            '60' => 1,
        ), $counter);
    }

    public function testRunEventsPersistence() {
        $currently = $this->_getCurrentDateTime();
        $context = 'foo';
        $persistence = $this->getMockBuilder('CM_Clockwork_Persistence')->setConstructorArgs(array($context))->setMethods(array('getLastRuntime',
                'setRuntime'))
            ->getMock();

        $manager = $this->getMockBuilder('CM_Clockwork_Manager')->setMethods(array('_getCurrentDateTime'))->getMockForAbstractClass();
        $manager->expects($this->any())->method('_getCurrentDateTime')->will($this->returnCallback(function () use ($currently) {
            return clone $currently;
        }));
        /** @var CM_Clockwork_Manager $manager */
        $manager->setPersistence($persistence);
        $counter = array(
            '1' => 0,
            '2' => 0,
        );
        $event1 = $this->_createEvent($manager, $currently, '1 second', $counter, 'event1');
        $event2 = $this->_createEvent($manager, $currently, '2 seconds', $counter, 'event2');

        $persistence->expects($this->at(0))->method('getLastRuntime')->with($event1)->will($this->returnValue(null));
        $persistence->expects($this->at(1))->method('getLastRuntime')->with($event2)->will($this->returnValue($this->_getCurrentDateTime()));
        $persistence->expects($this->at(2))->method('getLastRuntime')->with($event1)->will($this->returnValue(null));
        $persistence->expects($this->at(3))->method('getLastRuntime')->with($event2)->will($this->returnValue($this->_getCurrentDateTime()));
        $persistence->expects($this->at(4))->method('setRuntime')->with($event1, $this->_getCurrentDateTime(1));

        $persistence->expects($this->at(5))->method('getLastRuntime')->with($event1)->will($this->returnValue($this->_getCurrentDateTime(1)));
        $persistence->expects($this->at(6))->method('getLastRuntime')->with($event2)->will($this->returnValue($this->_getCurrentDateTime(0)));
        $persistence->expects($this->at(7))->method('setRuntime')->with($event1, $this->_getCurrentDateTime(2));
        $persistence->expects($this->at(8))->method('setRuntime')->with($event2, $this->_getCurrentDateTime(2));

        $persistence->expects($this->at(9))->method('getLastRuntime')->with($event1)->will($this->returnValue($this->_getCurrentDateTime(2)));
        $persistence->expects($this->at(10))->method('getLastRuntime')->with($event2)->will($this->returnValue($this->_getCurrentDateTime(2)));
        $persistence->expects($this->at(11))->method('setRuntime')->with($event1, $this->_getCurrentDateTime(3));

        $persistence->expects($this->at(12))->method('getLastRuntime')->with($event1)->will($this->returnValue($this->_getCurrentDateTime(3)));
        $persistence->expects($this->at(13))->method('getLastRuntime')->with($event2)->will($this->returnValue($this->_getCurrentDateTime(2)));
        $persistence->expects($this->at(14))->method('setRuntime')->with($event1, $this->_getCurrentDateTime(4));
        $persistence->expects($this->at(15))->method('setRuntime')->with($event2, $this->_getCurrentDateTime(4));

        $persistence->expects($this->at(16))->method('getLastRuntime')->with($event1)->will($this->returnValue($this->_getCurrentDateTime(4)));
        $persistence->expects($this->at(17))->method('getLastRuntime')->with($event2)->will($this->returnValue($this->_getCurrentDateTime(4)));
        $persistence->expects($this->at(18))->method('setRuntime')->with($event1, $this->_getCurrentDateTime(5));

        $persistence->expects($this->at(19))->method('getLastRuntime')->with($event1)->will($this->returnValue($this->_getCurrentDateTime(5)));
        $persistence->expects($this->at(20))->method('getLastRuntime')->with($event2)->will($this->returnValue($this->_getCurrentDateTime(4)));
        $persistence->expects($this->at(21))->method('setRuntime')->with($event1, $this->_getCurrentDateTime(6));
        $persistence->expects($this->at(22))->method('setRuntime')->with($event2, $this->_getCurrentDateTime(6));

        for ($i = 0; $i <= 6; $i++) {
            $manager->runEvents();
            $currently->add(new DateInterval('PT1S'));
        }
    }

    /**
     * @param int $delta
     * @return DateTime
     */
    protected function _getCurrentDateTime($delta = null) {
        $dateTime = new DateTime('midnight');
        if ($delta) {
            $dateTime->add(new DateInterval('PT' . $delta . 'S'));
        }
        return $dateTime;
    }

    /**
     * @param CM_Clockwork_Manager $manager
     * @param DateTime             $timeReference
     * @param string               $dateString
     * @param array                $counter
     * @param string               $name
     * @return CM_Clockwork_Event
     */
    private function _createEvent(CM_Clockwork_Manager $manager, DateTime $timeReference, $dateString, &$counter, $name) {
        $callback = function () use (&$counter, $dateString) {
            $counter[(int) $dateString]++;
        };
        $event = $this->getMockBuilder('CM_Clockwork_Event')->setMethods(array('_getCurrentDateTime'))->disableOriginalConstructor()->getMock();
        $event->expects($this->any())->method('_getCurrentDateTime')->will($this->returnCallback(function () use ($timeReference) {
            return clone $timeReference;
        }));
        /** @var CM_Clockwork_Event $event */
        $event->__construct($name, $dateString);
        $event->registerCallback($callback);
        $manager->registerEvent($event);
        return $event;
    }
}
