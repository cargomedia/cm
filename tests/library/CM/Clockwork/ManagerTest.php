<?php

class CM_Clockwork_ManagerTest extends CMTest_TestCase {

    public function testSetStorage() {
        $manager = new CM_Clockwork_Manager();
        $serviceManager = CM_Service_Manager::getInstance();
        $manager->setServiceManager($serviceManager);
        $storage = $this->mockObject('CM_Clockwork_Storage_Abstract', ['foo']);
        $methodMock = $storage->mockMethod('setServiceManager');
        $methodMock->set(function (CM_Service_Manager $manager) use ($serviceManager) {
            $this->assertEquals($serviceManager, $manager);
        });
        /** @var CM_Clockwork_Storage_Abstract $storage */
        $manager->setStorage($storage);
        $this->assertSame(1, $methodMock->getCallCount());
    }

    public function testRunEvents() {
        $currently = new DateTime();
        $eventMock = $this->mockClass('CM_Clockwork_Event');
        $eventRun = $eventMock->newInstanceWithoutConstructor();
        $runEventRun = $eventRun->mockMethod('run');
        /** @var CM_Clockwork_Event $eventRun */
        $eventNoRun =$eventMock->newInstanceWithoutConstructor();
        $runEventNoRun = $eventNoRun->mockMethod('run');
        /** @var CM_Clockwork_Event $eventNoRun */
        $manager = $this->mockObject('CM_Clockwork_Manager');
        $manager->mockMethod('_getCurrentDateTime')->set(function () use (&$currently) {
            return clone $currently;
        });
        $shouldRun = $manager->mockMethod('_shouldRun')->set(function(CM_Clockwork_Event $event) use ($eventRun, $eventNoRun) {
           return $event == $eventRun;
        });
        /** @var CM_Clockwork_Manager $manager */
        $currently->modify('10 seconds');
        $storage = $this->mockClass('CM_Clockwork_Storage_Abstract')->newInstanceWithoutConstructor();
        $setRuntime = $storage->mockMethod('setRuntime')->at(0, function(CM_Clockwork_Event $event, DateTime $runtime) use ($currently, $eventRun) {
            $this->assertEquals($currently, $runtime);
            $this->assertEquals($eventRun, $event);
        });
        /** @var CM_Clockwork_Storage_Abstract $storage */
        $manager->setServiceManager(CM_Service_Manager::getInstance());
        $manager->setStorage($storage);

        $manager->registerEvent($eventRun);
        $manager->registerEvent($eventNoRun);

        $manager->runEvents();
        $this->assertSame(1, $setRuntime->getCallCount());
        $this->assertSame(2, $shouldRun->getCallCount());
        $this->assertSame(1, $runEventRun->getCallCount());
        $this->assertSame(0, $runEventNoRun->getCallCount());
    }

    public function testShouldRunFixedTimeMode() {
        $timeZone = CM_Bootloader::getInstance()->getTimeZone();
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
        $event = new CM_Clockwork_Event('event1', '14:00');
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
        $event = new CM_Clockwork_Event('event2', 'first day of 09:00');
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
        $event = new CM_Clockwork_Event('event3', '23:59');

        $currently->modify('next day 00:01');
        $this->assertTrue($_shouldRun->invoke($manager, $event));

        $lastRuntime = clone($currently);
        $currently->modify('23:58');
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently->modify('23:59');
        $this->assertTrue($_shouldRun->invoke($manager, $event));
    }

    public function testShouldRunIntervalMode() {
        $currently = new DateTime('now', new DateTimeZone('UTC'));
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

    public function testShouldRun_intervalMode_backwardDaylightSavingTimeSwitch() {
        $this->_testIntervalModeDST(new DateTime('2014-10-26 00:20:00', new DateTimeZone('UTC')), new DateTimeZone('Europe/Berlin'));
    }

    public function testShouldRun_intervalMode_forwardDaylightSavingTimeSwitch() {
        $this->_testIntervalModeDST(new DateTime('2014-3-30 00:20:00', new DateTimeZone('UTC')), new DateTimeZone('Europe/Berlin'));
    }

    public function testShouldRun_intervalMode_backwardDaylightSavingTimeSwitch_NegativeOffsetTimeZone() {
        $this->_testIntervalModeDST(new DateTime('2014-11-2 06:20:00', new DateTimeZone('UTC')), new DateTimeZone('America/Chicago'));
    }

    public function testShouldRun_intervalMode_forwardDaylightSavingTimeSwitch_NegativeOffsetTimeZone() {
        $this->_testIntervalModeDST(new DateTime('2014-3-9 07:20:00', new DateTimeZone('UTC')), new DateTimeZone('America/Chicago'));
    }

    public function testShouldRun_fixedTimeMode_backwardDaylightSavingTimeSwitch() {
        $timeZone = new DateTimeZone('Europe/Berlin'); // gmt +1/+2
        $lastRuntime = null;
        $currently = new DateTime('2014-10-26 00:10:00', new DateTimeZone('UTC'));

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
        $event = new CM_Clockwork_Event('event', '02:10:00');

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

    public function testShouldRun_fixedTimeMode_forwardDaylightSavingTimeSwitch() {
        $timeZone = new DateTimeZone('Europe/Berlin'); // gmt +1/+2
        $lastRuntime = null;
        $currently = new DateTime('2014-03-30 01:09:59', new DateTimeZone('UTC'));

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
        $event = new CM_Clockwork_Event('event', '02:10:00');

        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));
        $lastRuntime = clone $currently;
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently = new DateTime('2014-03-31 00:09:59', new DateTimeZone('UTC'));
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));
    }

    public function testShouldRun_fixedTimeMode_backwardDaylightSavingTimeSwitch_NegativeOffsetTimeZone() {
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
        $event = new CM_Clockwork_Event('event', '01:10:00');

        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));
        $lastRuntime = clone $currently;
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently = new DateTime('2014-11-3 07:09:59', new DateTimeZone('UTC'));
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));
    }

    public function testShouldRun_fixedTimeMode_forwardDaylightSavingTimeSwitch_NegativeOffsetTimeZone() {
        $timeZone = new DateTimeZone('America/Chicago'); // gmt -6/-5
        $lastRuntime = null;
        $currently = new DateTime('2014-03-09 07:10:00', new DateTimeZone('UTC'));

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
        $event = new CM_Clockwork_Event('event', '02:10:00');

        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently->modify('08:09:59');
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently->modify('08:10:00');
        $this->assertTrue($_shouldRun->invoke($manager, $event));
        $lastRuntime = clone $currently;

        $currently = new DateTime('2014-03-09 08:10:00', new DateTimeZone('UTC'));
        $this->assertFalse($_shouldRun->invoke($manager, $event));

        $currently = new DateTime('2014-03-10 07:09:59', new DateTimeZone('UTC'));
        $this->assertFalse($_shouldRun->invoke($manager, $event));
        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event));
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    private function _getDateTime(DateTime $dateTime) {
        return new DateTime($dateTime->format('Y-m-d ') . ' ' . $dateTime->format('H:i:s') . ' +' . ($dateTime->getOffset() / 3600));
    }

    /**
     * @param DateTime     $start
     * @param DateTimeZone $timeZone
     * @throws CM_Exception_Invalid
     */
    private function _testIntervalModeDST(DateTime $start, DateTimeZone $timeZone) {
        $currently = clone $start;
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
        if ($timeZone->getOffset($start) === $timeZone->getOffset($currently)) {
            throw new CM_Exception_Invalid("Test did not go through a daylight saving time switch");
        }
    }
}
