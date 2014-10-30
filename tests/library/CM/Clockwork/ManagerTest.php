<?php

class CM_Clockwork_ManagerTest extends CMTest_TestCase {

    public function testSetStorage() {
        $manager = new CM_Clockwork_Manager();
        $serviceManager = CM_Service_Manager::getInstance();
        $manager->setServiceManager($serviceManager);
        $storage = $this->mockObject('CM_Clockwork_Storage_Abstract', ['foo']);
        $methodMock = $storage->mockMethod('setServiceManager');
        $methodMock->set(function(CM_Service_Manager $manager) use ($serviceManager) {
            $this->assertEquals($serviceManager, $manager);
        });
        /** @var CM_Clockwork_Storage_Abstract $storage */
        $manager->setStorage($storage);
        $this->assertSame(1, $methodMock->getCallCount());
    }

    public function testRunEventsFor() {
        $currently = $this->_getCurrentDateTime();
        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use ($currently) {
            return clone $currently;
        });
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
        $manager->setServiceManager(CM_Service_Manager::getInstance());
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

        $manager = $this->getMockBuilder('CM_Clockwork_Manager')->setMethods(array('_getCurrentDateTimeUTC'))->disableOriginalConstructor()->getMock();
        $manager->expects($this->any())->method('_getCurrentDateTimeUTC')->will($this->returnCallback(function () use ($currently) {
            return clone $currently;
        }));
        $manager->__construct();
        /** @var CM_Clockwork_Manager $manager */
        $manager->setServiceManager(CM_Service_Manager::getInstance());
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

    public function testShouldRunFixedTimeMode() {
        $timeZone = new DateTimeZone('UTC');
        $currently = null;
        $lastRuntime = null;
        $storageClass = $this->mockClass('CM_Clockwork_Storage_Memory');
        $storageClass->mockMethod('getLastRuntime')->set(function () use (&$lastRuntime) {
            if ($lastRuntime instanceof DateTime) {
                return clone $lastRuntime;
            }
            return $lastRuntime;
        });
        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use (&$currently) {
            return clone $currently;
        });
        $_shouldRun = CMTest_TH::getProtectedMethod('CM_Clockwork_Manager', '_shouldRun');
        /** @var CM_Clockwork_Storage_FileSystem $storage */
        $storage = $storageClass->newInstance();

        $currently = new DateTime('13:59:59', $timeZone);
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
        $manager->setServiceManager(CM_Service_Manager::getInstance());
        $manager->setStorage($storage);
        /** @var CM_Clockwork_Event $event */
        $event = new CM_Clockwork_Event('event1', '14:00', 'day');
        $currently->modify('13:59:59');
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

        $lastRuntime = clone($currently);
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently->modify('next day 13:59:59');
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

        // manager started after scheduled execution
        $lastRuntime = null;
        $currently = new DateTime('last day of', $timeZone);
        $manager = $managerMock->newInstance();
        $manager->setServiceManager(CM_Service_Manager::getInstance());
        $manager->setStorage($storage);
        $manager->setTimeZone($timeZone);
        $event = new CM_Clockwork_Event('event2', 'first day of 09:00', 'month');
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently->modify('next day 08:59:59');
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('09:00:00');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

        // execution delayed into next timeframe

        $lastRuntime = null;
        $currently = new DateTime('23:59', $timeZone);

        $manager = $managerMock->newInstance();
        $manager->setServiceManager(CM_Service_Manager::getInstance());
        $manager->setStorage($storage);
        $manager->setTimeZone($timeZone);
        $event = new CM_Clockwork_Event('event3', '23:59', 'day');

        $currently->modify('next day 00:01');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

        $lastRuntime = clone($currently);
        $currently->modify('23:58');
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently->modify('23:59');
        $this->assertTrue($_shouldRun->invoke($manager, $event));
    }

    public function testShouldRunIntervalMode() {
        $currently = $this->_getCurrentDateTime();
        $lastRuntime = null;
        $storageClass = $this->mockClass('CM_Clockwork_Storage_Memory');
        $storageClass->mockMethod('getLastRuntime')->set(function () use (&$lastRuntime) {
            if ($lastRuntime instanceof DateTime) {
                return clone $lastRuntime;
            }
            return $lastRuntime;
        });
        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use (&$currently) {
            return clone $currently;
        });
        /** @var CM_Clockwork_Storage_FileSystem $storage */
        $storage = $storageClass->newInstance();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
        $manager->setServiceManager(CM_Service_Manager::getInstance());
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
        $dateTime = new DateTime('midnight', new DateTimeZone('UTC'));
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
        $event = $this->getMockBuilder('CM_Clockwork_Event')->setMethods(array('_getCurrentDateTimeUTC'))->disableOriginalConstructor()->getMock();
        $event->expects($this->any())->method('_getCurrentDateTimeUTC')->will($this->returnCallback(function () use ($timeReference) {
            return clone $timeReference;
        }));
        /** @var CM_Clockwork_Event $event */
        $event->__construct($name, $dateString);
        $event->registerCallback($callback);
        $manager->registerEvent($event);
        return $event;
    }

    public function testShouldRun_intervalMode_backwardDaylightSavingTimeSwitch() {
        $lastRuntime = null;
        $timeZone = new DateTimeZone('Europe/Berlin');
        $currently = new DateTime('2014-10-26 00:20:00', new DateTimeZone('UTC'));
        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use (&$currently) {
            return clone $currently;
        });
        $storageClass = $this->mockClass('CM_Clockwork_Storage_Memory');
        $storageClass->mockMethod('getLastRuntime')->set(function () use (&$lastRuntime) {
            if ($lastRuntime instanceof DateTime) {
                return clone $lastRuntime;
            }
            return $lastRuntime;
        });
        /** @var CM_Clockwork_Storage_FileSystem $storage */
        $storage = $storageClass->newInstance();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
        $manager->setServiceManager(CM_Service_Manager::getInstance());
        $manager->setStorage($storage);
        $manager->setTimeZone($timeZone);
        $_shouldRun = CMTest_TH::getProtectedMethod('CM_Clockwork_Manager', '_shouldRun');
        $event = new CM_Clockwork_Event('event', '20 minutes');

        $lastRuntime = null;
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('19 minutes 59 seconds');
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

        $lastRuntime = clone $currently;
        $currently->modify('19 minutes 59 seconds');
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

        $lastRuntime = clone $currently;
        $currently->modify('19 minutes 59 seconds');
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));
    }

    public function testShouldRun_intervalMode_forwardDaylightSavingTimeSwitch() {
        $lastRuntime = null;
        $timeZone = new DateTimeZone('Europe/Berlin');
        $currently = new DateTime('2014-3-30 00:59:59', new DateTimeZone('UTC'));
        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use (&$currently) {
            return clone $currently;
        });
        $storageClass = $this->mockClass('CM_Clockwork_Storage_Memory');
        $storageClass->mockMethod('getLastRuntime')->set(function () use (&$lastRuntime) {
            if ($lastRuntime instanceof DateTime) {
                return clone $lastRuntime;
            }
            return $lastRuntime;
        });
        /** @var CM_Clockwork_Storage_FileSystem $storage */
        $storage = $storageClass->newInstance();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
        $manager->setServiceManager(CM_Service_Manager::getInstance());
        $manager->setStorage($storage);
        $manager->setTimeZone($timeZone);
        $_shouldRun = CMTest_TH::getProtectedMethod('CM_Clockwork_Manager', '_shouldRun');
        $event = new CM_Clockwork_Event('event', '20 minutes');

        $lastRuntime = null;
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('19 minutes 59 seconds');
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

        $lastRuntime = clone $currently;
        $currently->modify('19 minutes 59 seconds');
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

        $lastRuntime = clone $currently;
        $currently->modify('19 minutes 59 seconds');
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));
    }

    public function testShouldRun_intervalMode_backwardDaylightSavingTimeSwitch_NegativeOffsetTimeZone() {
        $lastRuntime = null;
        $timeZone = new DateTimeZone('America/Chicago');
        $currently = new DateTime('2014-11-1 06:20:00', new DateTimeZone('UTC'));
        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use (&$currently) {
            return clone $currently;
        });
        $storageClass = $this->mockClass('CM_Clockwork_Storage_Memory');
        $storageClass->mockMethod('getLastRuntime')->set(function () use (&$lastRuntime) {
            if ($lastRuntime instanceof DateTime) {
                return clone $lastRuntime;
            }
            return $lastRuntime;
        });
        /** @var CM_Clockwork_Storage_FileSystem $storage */
        $storage = $storageClass->newInstance();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
        $manager->setServiceManager(CM_Service_Manager::getInstance());
        $manager->setStorage($storage);
        $manager->setTimeZone($timeZone);
        $_shouldRun = CMTest_TH::getProtectedMethod('CM_Clockwork_Manager', '_shouldRun');
        $event = new CM_Clockwork_Event('event', '20 minutes');

        $lastRuntime = null;
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('19 minutes 59 seconds');
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

        $lastRuntime = clone $currently;
        $currently->modify('19 minutes 59 seconds');
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

        $lastRuntime = clone $currently;
        $currently->modify('19 minutes 59 seconds');
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));
    }

    public function testShouldRun_fixedTimeMode_backwardDaylightSavingTimeSwitch() {
        $timeZone = new DateTimeZone('Europe/Berlin'); // gmt +1/+2
        $lastRuntime = null;
        $currently = new DateTime('2014-10-25 21:00:00', new DateTimeZone('UTC'));

        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use (&$currently) {
            return clone $currently;
        });
        $storageClass = $this->mockClass('CM_Clockwork_Storage_Memory');
        $storageClass->mockMethod('getLastRuntime')->set(function () use (&$lastRuntime) {
            if ($lastRuntime instanceof DateTime) {
                return clone $lastRuntime;
            }
            return $lastRuntime;
        });
        /** @var CM_Clockwork_Storage_FileSystem $storage */
        $storage = $storageClass->newInstance();

        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
        $manager->setServiceManager(CM_Service_Manager::getInstance());
        $manager->setStorage($storage);
        $manager->setTimeZone($timeZone);
        $_shouldRun = CMTest_TH::getProtectedMethod('CM_Clockwork_Manager', '_shouldRun');
        $event = new CM_Clockwork_Event('event', '02:10:00', 'day');

        $currently = new DateTime('2014-10-26 00:10:00', new DateTimeZone('UTC'));
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently->modify('01:09:59');
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently->modify('01:10:00');
        $this->assertTrue($_shouldRun->invoke($manager, $event));
        $lastRuntime = clone $currently;

        $currently = new DateTime('2014-10-26 01:10:00', new DateTimeZone('UTC'));
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently = new DateTime('2014-10-27 01:09:59', new DateTimeZone('UTC'));
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));
    }

    public function testShouldRun_fixedTimeMode_backwardDaylightSavingTimeSwitchNegativeOffsetTimeZone() {
        $lastRuntime = null;
        $timeZone = new DateTimeZone('America/Chicago'); // gmt -6/-5
        $currently = new DateTime('2014-11-2 06:09:59', new DateTimeZone('UTC'));

        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use (&$currently) {
            return clone $currently;
        });
        $storageClass = $this->mockClass('CM_Clockwork_Storage_Memory');
        $storageClass->mockMethod('getLastRuntime')->set(function () use (&$lastRuntime) {
            if ($lastRuntime instanceof DateTime) {
                return clone $lastRuntime;
            }
            return $lastRuntime;
        });
        /** @var CM_Clockwork_Storage_FileSystem $storage */
        $storage = $storageClass->newInstance();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
        $manager->setServiceManager(CM_Service_Manager::getInstance());
        $manager->setStorage($storage);
        $manager->setTimeZone($timeZone);
        $_shouldRun = CMTest_TH::getProtectedMethod('CM_Clockwork_Manager', '_shouldRun');
        $event = new CM_Clockwork_Event('event', '01:10:00', 'day');

        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));
        $lastRuntime = clone $currently;

        $currently = new DateTime('2014-11-2 07:10:00', new DateTimeZone('UTC'));

        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently = new DateTime('2014-11-3 07:09:59', new DateTimeZone('UTC'));
        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));
    }

    protected function _getDateTime(DateTime $dateTime) {
        return new DateTime($dateTime->format('Y-m-d ') . ' ' . $dateTime->format('H:i:s') . ' +' . ($dateTime->getOffset() / 3600));
    }
}
