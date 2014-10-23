<?php

class CM_Clockwork_ManagerTest extends CMTest_TestCase {

    public function testRunEventsFor() {
        $currently = $this->_getCurrentDateTime();
        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $managerMock->mockMethod('_getCurrentDateTime')->set(function () use ($currently) {
            return clone $currently;
        });
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
        $counter = array(
            '1'  => 0,
            '2'  => 0,
            '5'  => 0,
            '60' => 0,
        );
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
        /** @var CM_Clockwork_Storage_Memory $storage */
        $storage = $this->getMockBuilder('CM_Clockwork_Storage_Memory')->setMethods(array('getLastRuntime',
            'setRuntime'))
            ->getMock();

        $manager = $this->getMockBuilder('CM_Clockwork_Manager')->setMethods(array('_getCurrentDateTime'))->disableOriginalConstructor()->getMock();
        $manager->expects($this->any())->method('_getCurrentDateTime')->will($this->returnCallback(function () use ($currently) {
            return clone $currently;
        }));
        $manager->__construct();
        /** @var CM_Clockwork_Manager $manager */
        $manager->setStorage($storage);
        $counter = array(
            '1' => 0,
            '2' => 0,
        );
        $event1 = $this->_createEvent($manager, $currently, '1 second', $counter, 'event1');
        $event2 = $this->_createEvent($manager, $currently, '2 seconds', $counter, 'event2');

        $storage->expects($this->at(0))->method('getLastRuntime')->with($event1)->will($this->returnValue(null));
        $storage->expects($this->at(1))->method('getLastRuntime')->with($event2)->will($this->returnValue($this->_getCurrentDateTime()));

        $storage->expects($this->at(2))->method('getLastRuntime')->with($event1)->will($this->returnValue(null));
        $storage->expects($this->at(3))->method('getLastRuntime')->with($event2)->will($this->returnValue($this->_getCurrentDateTime()));
        $storage->expects($this->at(4))->method('setRuntime')->with($event1, $this->_getCurrentDateTime(1));

        $storage->expects($this->at(5))->method('getLastRuntime')->with($event1)->will($this->returnValue($this->_getCurrentDateTime(1)));
        $storage->expects($this->at(6))->method('getLastRuntime')->with($event2)->will($this->returnValue($this->_getCurrentDateTime(0)));
        $storage->expects($this->at(7))->method('setRuntime')->with($event1, $this->_getCurrentDateTime(2));
        $storage->expects($this->at(8))->method('setRuntime')->with($event2, $this->_getCurrentDateTime(2));

        $storage->expects($this->at(9))->method('getLastRuntime')->with($event1)->will($this->returnValue($this->_getCurrentDateTime(2)));
        $storage->expects($this->at(10))->method('getLastRuntime')->with($event2)->will($this->returnValue($this->_getCurrentDateTime(2)));
        $storage->expects($this->at(11))->method('setRuntime')->with($event1, $this->_getCurrentDateTime(3));

        $storage->expects($this->at(12))->method('getLastRuntime')->with($event1)->will($this->returnValue($this->_getCurrentDateTime(3)));
        $storage->expects($this->at(13))->method('getLastRuntime')->with($event2)->will($this->returnValue($this->_getCurrentDateTime(2)));
        $storage->expects($this->at(14))->method('setRuntime')->with($event1, $this->_getCurrentDateTime(4));
        $storage->expects($this->at(15))->method('setRuntime')->with($event2, $this->_getCurrentDateTime(4));

        $storage->expects($this->at(16))->method('getLastRuntime')->with($event1)->will($this->returnValue($this->_getCurrentDateTime(4)));
        $storage->expects($this->at(17))->method('getLastRuntime')->with($event2)->will($this->returnValue($this->_getCurrentDateTime(4)));
        $storage->expects($this->at(18))->method('setRuntime')->with($event1, $this->_getCurrentDateTime(5));

        $storage->expects($this->at(19))->method('getLastRuntime')->with($event1)->will($this->returnValue($this->_getCurrentDateTime(5)));
        $storage->expects($this->at(20))->method('getLastRuntime')->with($event2)->will($this->returnValue($this->_getCurrentDateTime(4)));
        $storage->expects($this->at(21))->method('setRuntime')->with($event1, $this->_getCurrentDateTime(6));
        $storage->expects($this->at(22))->method('setRuntime')->with($event2, $this->_getCurrentDateTime(6));

        for ($i = 0; $i <= 6; $i++) {
            $manager->runEvents();
            $currently->add(new DateInterval('PT1S'));
        }
    }

    public function testShouldRunFixedTime() {
        $currently = new DateTime();
        $lastRuntime = null;
        $storageClass = $this->mockClass('CM_Clockwork_Storage_Memory');
        $storageClass->mockMethod('getLastRuntime')->set(function () use (&$lastRuntime) {
            if ($lastRuntime instanceof DateTime) {
                return clone $lastRuntime;
            }
            return $lastRuntime;
        });
        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $managerMock->mockMethod('_getCurrentDateTime')->set(function () use (&$currently) {
            return clone $currently;
        });
        /** @var CM_Clockwork_Storage_FileSystem $storage */
        $storage = $storageClass->newInstance();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
        $manager->setStorage($storage);
        $_shouldRun = CMTest_TH::getProtectedMethod('CM_Clockwork_Manager', '_shouldRun');

        /** @var CM_Clockwork_Event $event */
        $event = new CM_Clockwork_Event('event1', '14:00');
        $currently->modify('13:59:59');
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently->modify('2 seconds');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

        $lastRuntime = clone($currently);
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently->modify('1 day');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

        $lastRuntime = null;
        $event = new CM_Clockwork_Event('event2', 'first day of 09:00');
        $currently = new DateTime('first day of 08:59');
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently->modify('last day of');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

        $lastRuntime = clone($currently);
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently->modify('next day 09:00');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

        //execution delayed into next timeframe

        $lastRuntime = null;
        $event = new CM_Clockwork_Event('event3', '23:59');
        $currently = new DateTime('23:59');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

        $lastRuntime = clone($currently);
        $currently->modify('2 days 00:01');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

        $lastRuntime = clone($currently);
        $currently->modify('23:58');
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently->modify('23:59');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

    }

    public function testShouldRunInterval() {
        $currently = new DateTime();
        $lastRuntime = null;
        $storageClass = $this->mockClass('CM_Clockwork_Storage_Memory');
        $storageClass->mockMethod('getLastRuntime')->set(function () use (&$lastRuntime) {
            if ($lastRuntime instanceof DateTime) {
                return clone $lastRuntime;
            }
            return $lastRuntime;
        });
        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $managerMock->mockMethod('_getCurrentDateTime')->set(function () use (&$currently) {
            return clone $currently;
        });
        /** @var CM_Clockwork_Storage_FileSystem $storage */
        $storage = $storageClass->newInstance();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
        $manager->setStorage($storage);
        $_shouldRun = CMTest_TH::getProtectedMethod('CM_Clockwork_Manager', '_shouldRun');

        $event = new CM_Clockwork_Event('event', '2 seconds');
        $lastRuntime = null;
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('2 seconds');
        $this->assertTrue($_shouldRun->invoke($manager, $event));
        $lastRuntime = clone($currently);

        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('1 second');
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));
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
