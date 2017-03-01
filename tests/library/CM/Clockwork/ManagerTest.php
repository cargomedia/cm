<?php

class CM_Clockwork_ManagerTest extends CMTest_TestCase {

    public function testRegisterEvent() {
        $manager = new CM_Clockwork_Manager();
        $manager->registerEvent(new CM_Clockwork_Event('foo', '1 second'));
        $manager->registerEvent(new CM_Clockwork_Event('bar', '1 second'));
        try {
            $manager->registerEvent(new CM_Clockwork_Event('foo', '1 second'));
            $this->fail('Registered duplicate event');
        } catch (CM_Exception $ex) {
            $this->assertSame('Duplicate event-name', $ex->getMessage());
        }
    }

    public function testRegisterEventCallbackWithLastRuntime() {
        $process = $this->mockClass('CM_Process')->newInstanceWithoutConstructor();
        $forkMock = $process->mockMethod('fork');
        $forkMock->set(function ($callback) use ($forkMock) {
            $callback();
            $forkHandler = $this->mockClass('CM_Process_ForkHandler')->newInstanceWithoutConstructor();
            $forkHandler->mockMethod('getIdentifier')->set($forkMock->getCallCount() + 1);
            return $forkHandler;
        });

        /** @var CM_Clockwork_Storage_Abstract $storage */
        $storage = new CM_Clockwork_Storage_Memory();
        $currently = new DateTime('midnight', new DateTimeZone('UTC'));

        /** @var CM_Clockwork_Manager $manager */
        $manager = $this->mockObject('CM_Clockwork_Manager');
        $manager->mockMethod('_shouldRun')->set(true);
        $manager->mockMethod('_getProcess')->set($process);
        $manager->mockMethod('_getCurrentDateTimeUTC')->set(function () use (&$currently) {
            return $currently;
        });
        $manager->setStorage($storage);

        $event = new CM_Clockwork_Event('bar', '1 second');
        $manager->registerEvent($event);

        $lastRuntimeActual = null;
        $callbackCallCount = 0;
        $event->registerCallback(function ($lastRuntime) use (&$lastRuntimeActual, &$callbackCallCount) {
            $lastRuntimeActual = $lastRuntime;
            $callbackCallCount++;
        });

        $process->mockMethod('listenForChildren')->set([1 => new CM_Process_Result(0)]);
        $manager->runEvents();
        $this->assertSame(1, $callbackCallCount);
        $this->assertNull($lastRuntimeActual);

        // check if we get the correct lastRuntime
        $expectedLastRuntime = clone $currently;
        $currently->modify('10 seconds');
        $process->mockMethod('listenForChildren')->set([2 => new CM_Process_Result(0)]);
        $manager->runEvents();
        $this->assertSame(2, $callbackCallCount);
        $this->assertSameTime($expectedLastRuntime, $lastRuntimeActual);

        // check if an event callback can be declared without the $lastRuntime argument
        $event->registerCallback(function () use (&$secondCallbackCallCount) {
            $secondCallbackCallCount++;
        });
        $expectedLastRuntime = clone $currently;
        $currently->modify('10 seconds');
        $secondCallbackCallCount = 0;
        $process->mockMethod('listenForChildren')->set([3 => new CM_Process_Result(0)]);
        $manager->runEvents();
        $this->assertSame(1, $secondCallbackCallCount);
        $this->assertSame(3, $callbackCallCount);
        $this->assertSameTime($expectedLastRuntime, $lastRuntimeActual);

        // check if error in event callback will not update the lastRuntime
        $expectedLastRuntime = clone $currently;
        $currently->modify('10 seconds');
        $process->mockMethod('listenForChildren')->set([4 => new CM_Process_Result(1)]);
        $manager->runEvents();
        $this->assertSame(4, $callbackCallCount);
        $this->assertSameTime($expectedLastRuntime, $lastRuntimeActual);

        $process->mockMethod('listenForChildren')->set([5 => new CM_Process_Result(0)]);
        $manager->runEvents();
        $this->assertSame(5, $callbackCallCount);
        $this->assertSameTime($expectedLastRuntime, $lastRuntimeActual);

        $process->mockMethod('listenForChildren')->set([6 => new CM_Process_Result(0)]);
        $manager->runEvents();
        $this->assertSame(6, $callbackCallCount);
        $this->assertSameTime($currently, $lastRuntimeActual);
    }

    public function testRunNonBlocking() {
        $process = $this->mockClass('CM_Process')->newInstanceWithoutConstructor();
        $forkMock = $process->mockMethod('fork');
        $forkMock->set(function () use ($forkMock) {
            $forkHandler = $this->mockClass('CM_Process_ForkHandler')->newInstanceWithoutConstructor();
            $forkHandler->mockMethod('getIdentifier')->set($forkMock->getCallCount() + 1);
            return $forkHandler;
        });
        $manager = $this->mockObject('CM_Clockwork_Manager');
        $manager->mockMethod('_shouldRun')->set(true);
        $manager->mockMethod('_getProcess')->set($process);
        /** @var CM_Clockwork_Manager $manager */
        $event1 = new CM_Clockwork_Event('1', '1 second');
        $manager->registerEvent($event1);
        $event2 = new CM_Clockwork_Event('2', '1 second');
        $manager->registerEvent($event2);
        $process->mockMethod('listenForChildren')->set([]); // no events finish

        $this->assertFalse(CMTest_TH::callProtectedMethod($manager, '_isRunning', [$event1]));
        $this->assertFalse(CMTest_TH::callProtectedMethod($manager, '_isRunning', [$event2]));

        $manager->runEvents();
        $this->assertSame(2, $forkMock->getCallCount());
        $this->assertTrue(CMTest_TH::callProtectedMethod($manager, '_isRunning', [$event1]));
        $this->assertTrue(CMTest_TH::callProtectedMethod($manager, '_isRunning', [$event2]));

        // event 2 finishes
        $process->mockMethod('listenForChildren')->set([2 => new CM_Process_Result(0)]);
        $manager->runEvents();
        $this->assertSame(2, $forkMock->getCallCount());
        $this->assertTrue(CMTest_TH::callProtectedMethod($manager, '_isRunning', [$event1]));
        $this->assertFalse(CMTest_TH::callProtectedMethod($manager, '_isRunning', [$event2]));

        // no events finish, event 2 starts
        $process->mockMethod('listenForChildren')->set([]);
        $manager->runEvents();
        $this->assertSame(3, $forkMock->getCallCount());
        $this->assertTrue(CMTest_TH::callProtectedMethod($manager, '_isRunning', [$event1]));
        $this->assertTrue(CMTest_TH::callProtectedMethod($manager, '_isRunning', [$event2]));

        // both events finish, event 2 finishes with an error
        $process->mockMethod('listenForChildren')->set([1 => new CM_Process_Result(0),
                                                        3 => new CM_Process_Result(1)]);
        $manager->runEvents();

        $this->assertFalse(CMTest_TH::callProtectedMethod($manager, '_isRunning', [$event1]));
        $this->assertFalse(CMTest_TH::callProtectedMethod($manager, '_isRunning', [$event2]));
    }

    public function testIntervalEventsNotPushedBackByClockworkRestart() {
        // infinite pushback on restart problem for long intervals
        $currently = new DateTime('midnight', new DateTimeZone('UTC'));
        $lastRuntime = null;
        $process = $this->mockClass('CM_Process')->newInstanceWithoutConstructor();
        $forkMock = $process->mockMethod('fork');
        $forkMock->set(function () use ($forkMock) {
            $forkHandler = $this->mockClass('CM_Process_ForkHandler')->newInstanceWithoutConstructor();
            $forkHandler->mockMethod('getIdentifier')->set($forkMock->getCallCount() + 1);
            return $forkHandler;
        });
        $storage = new CM_Clockwork_Storage_Memory();
        /** @var CM_Clockwork_Storage_Abstract $storage */
        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use ($currently) {
            return clone $currently;
        });
        $managerMock->mockMethod('_getProcess')->set($process);
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
        $startTime = clone $currently;
        $manager->setStorage($storage);
        $_shouldRun = CMTest_TH::getProtectedMethod('CM_Clockwork_Manager', '_shouldRun');

        $event1 = new CM_Clockwork_Event('event1', '5 seconds');
        $event2 = new CM_Clockwork_Event('event2', '01:00');
        $manager->registerEvent($event1);
        $manager->registerEvent($event2);

        $currently->modify('4 seconds');
        $this->assertFalse($_shouldRun->invoke($manager, $event1));
        $this->assertFalse($_shouldRun->invoke($manager, $event2));
        $process->mockMethod('listenForChildren')->set([]);
        $manager->runEvents();
        $this->assertEquals($startTime, $storage->getLastRuntime($event1));
        $this->assertNull($storage->getLastRuntime($event2));

        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use ($currently) {
            return clone $currently;
        });
        $manager = $managerMock->newInstance();
        $manager->setStorage($storage);
        $manager->registerEvent($event1);

        $this->assertFalse($_shouldRun->invoke($manager, $event1));
        $manager->runEvents();
        $this->assertEquals($startTime, $storage->getLastRuntime($event1));

        $currently->modify('1 second');
        $this->assertTrue($_shouldRun->invoke($manager, $event1));
        $process->mockMethod('listenForChildren')->set([1 => new CM_Process_Result(0)]);
        $manager->runEvents();
        $this->assertEquals($currently, $storage->getLastRuntime($event1));
        $this->assertFalse($_shouldRun->invoke($manager, $event1));
    }

    public function testShouldRunFixedTimeMode() {
        $timeZone = CM_Bootloader::getInstance()->getTimeZone();
        $currently = new DateTime('midnight', new DateTimeZone('UTC'));
        $lastRuntime = null;
        $storageClass = $this->mockClass('CM_Clockwork_Storage_Abstract');
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
        $storage = $storageClass->newInstanceWithoutConstructor();

        $currently = new DateTime('13:59:59', $timeZone);
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
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
        $storageClass = $this->mockClass('CM_Clockwork_Storage_Abstract');
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
        /** @var CM_Clockwork_Storage_Abstract $storage */
        $storage = $storageClass->newInstanceWithoutConstructor();
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
        $storageClass = $this->mockClass('CM_Clockwork_Storage_Abstract');
        $storageClass->mockMethod('getLastRuntime')->set(function () use (&$lastRuntime) {
            if ($lastRuntime instanceof DateTime) {
                return clone $lastRuntime;
            }
            return $lastRuntime;
        });
        /** @var CM_Clockwork_Storage_FileSystem $storage */
        $storage = $storageClass->newInstanceWithoutConstructor();

        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
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
        $storageClass = $this->mockClass('CM_Clockwork_Storage_Abstract');
        $storageClass->mockMethod('getLastRuntime')->set(function () use (&$lastRuntime) {
            if ($lastRuntime instanceof DateTime) {
                return clone $lastRuntime;
            }
            return $lastRuntime;
        });
        /** @var CM_Clockwork_Storage_FileSystem $storage */
        $storage = $storageClass->newInstanceWithoutConstructor();

        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
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
        $storageClass = $this->mockClass('CM_Clockwork_Storage_Abstract');
        $storageClass->mockMethod('getLastRuntime')->set(function () use (&$lastRuntime) {
            if ($lastRuntime instanceof DateTime) {
                return clone $lastRuntime;
            }
            return $lastRuntime;
        });
        /** @var CM_Clockwork_Storage_FileSystem $storage */
        $storage = $storageClass->newInstanceWithoutConstructor();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
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
        $storageClass = $this->mockClass('CM_Clockwork_Storage_Abstract');
        $storageClass->mockMethod('getLastRuntime')->set(function () use (&$lastRuntime) {
            if ($lastRuntime instanceof DateTime) {
                return clone $lastRuntime;
            }
            return $lastRuntime;
        });
        /** @var CM_Clockwork_Storage_FileSystem $storage */
        $storage = $storageClass->newInstanceWithoutConstructor();

        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
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

    public function testStartTerminatable() {
        $process = CM_Process::getInstance();
        $forkHandler = $process->fork(function () {
            $clockworkManager = new CM_Clockwork_Manager();
            $clockworkManager->start();
        });

        usleep(1000000 * 0.1);
        $process->listenForChildren();
        $this->assertSame(true, $process->isRunning($forkHandler->getPid()));

        posix_kill($forkHandler->getPid(), SIGTERM);

        usleep(1000000 * 0.1);
        $process->listenForChildren();
        $this->assertSame(false, $process->isRunning($forkHandler->getPid()));
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
        $storageClass = $this->mockClass('CM_Clockwork_Storage_Abstract');
        $storageClass->mockMethod('getLastRuntime')->set(function () use (&$lastRuntime) {
            if ($lastRuntime instanceof DateTime) {
                return clone $lastRuntime;
            }
            return $lastRuntime;
        });
        /** @var CM_Clockwork_Storage_FileSystem $storage */
        $storage = $storageClass->newInstanceWithoutConstructor();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance();
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
