<?php

class CM_Clockwork_ManagerTest extends CMTest_TestCase {

    public function testRegisterEvent() {
        $manager = new CM_Clockwork_Manager(new CM_EventHandler_EventHandler());
        $manager->registerEvent(new CM_Clockwork_Event('foo', '1 second'));
        $manager->registerEvent(new CM_Clockwork_Event('bar', '1 second'));
        try {
            $manager->registerEvent(new CM_Clockwork_Event('foo', '1 second'));
            $this->fail('Registered duplicate event');
        } catch (CM_Exception $ex) {
            $this->assertSame('Duplicate event-name', $ex->getMessage());
        }
    }

    public function testEventTriggeringParameters() {
        $eventHandler = new CM_EventHandler_EventHandler();
        $clockwork = new CM_Clockwork_Manager($eventHandler);
        $event = new CM_Clockwork_Event('foo', '1 second');
        $currently = DateTime::createFromFormat('U', time());
        $lastRuntime = DateTime::createFromFormat('U', time() - 1);
        $eventCounter = $this->_registerEvent($clockwork, $eventHandler, $event, function (CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) use ($currently, $lastRuntime) {
            $this->assertSame('foo', $event->getName());
            $this->assertSame(true, $status->isRunning());
            $this->assertEquals($lastRuntime, $status->getLastRuntime());
            $this->assertEquals($currently, $status->getLastStartTime());
        });
        $storage = new CM_Clockwork_Storage_Memory();
        $clockwork->setStorage($storage);

        $storage->setStatus($event, (new CM_Clockwork_Event_Status())->setLastRuntime($lastRuntime));
        $clockwork->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());
    }

    public function testIntervalEventsNotPushedBackByClockworkRestart() {
        // infinite pushback on restart problem for long intervals
        $currently = new DateTime('midnight', new DateTimeZone('UTC'));
        $lastRuntime = null;
        $storage = new CM_Clockwork_Storage_Memory();
        /** @var CM_Clockwork_Storage_Abstract $storage */
        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use ($currently) {
            return clone $currently;
        });
        $eventHandler = new CM_EventHandler_EventHandler();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance([$eventHandler]);
        $startTime = clone $currently;
        $manager->setStorage($storage);

        $event1 = new CM_Clockwork_Event('event1', '5 seconds');
        $event1Counter = $this->_registerEvent($manager, $eventHandler, $event1, function (CM_Clockwork_Event $event) use ($manager) {
            $manager->setCompleted($event);
        });
        $event2 = new CM_Clockwork_Event('event2', '01:00');
        $event2Counter = $this->_registerEvent($manager, $eventHandler, $event2, function (CM_Clockwork_Event $event) use ($manager) {
            $manager->setCompleted($event);
        });

        $currently->modify('4 seconds');
        $manager->runEvents();
        $this->assertSame(0, $event1Counter->getCallCount());
        $this->assertSame(0, $event2Counter->getCallCount());
        $this->assertEquals($startTime, $storage->getStatus($event1)->getLastRuntime());
        $this->assertSame(null, $storage->getStatus($event1)->getLastStartTime());
        $this->assertSame(null, $storage->getStatus($event2)->getLastRuntime());
        $this->assertSame(null, $storage->getStatus($event2)->getLastStartTime());

        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use ($currently) {
            return clone $currently;
        });
        $manager = $managerMock->newInstance([$eventHandler]);
        $manager->setStorage($storage);
        $manager->registerEvent($event1);

        $manager->runEvents();
        $this->assertSame(0, $event1Counter->getCallCount());
        $this->assertEquals($startTime, $storage->getStatus($event1)->getLastRuntime());
        $this->assertSame(null, $storage->getStatus($event1)->getLastStartTime());

        $currently->modify('1 second');
        $manager->runEvents();
        $this->assertSame(1, $event1Counter->getCallCount());
        $this->assertEquals($currently, $storage->getStatus($event1)->getLastStartTime());
        $this->assertEquals($currently, $storage->getStatus($event1)->getLastStartTime());
    }

    public function testSchedulingFixedTimeMode() {
        $timeZone = CM_Bootloader::getInstance()->getTimeZone();
        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $currently = new DateTime('13:59:59', $timeZone);
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use (&$currently) {
            return clone $currently;
        });
        $storage = new CM_Clockwork_Storage_Memory();
        $eventHandler = new CM_EventHandler_EventHandler();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance([$eventHandler]);
        $manager->setStorage($storage);
        /** @var CM_Clockwork_Event $event */
        $event = new CM_Clockwork_Event('event1', '14:00');
        $eventCounter = $this->_registerEvent($manager, $eventHandler, $event, function (CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) use ($manager) {
            $manager->setCompleted($event);
        });

        $manager->runEvents();
        $this->assertSame(0, $eventCounter->getCallCount());

        $currently->modify('1 second');
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());

        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());

        $currently->modify('next day 13:59:59');
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());

        $currently->modify('1 second');
        $manager->runEvents();
        $this->assertSame(2, $eventCounter->getCallCount());
    }

    public function testSchedulingFixedTimeMode_ManagerStartedAfterScheduledExecution() {
        $timeZone = CM_Bootloader::getInstance()->getTimeZone();
        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $currently = new DateTime('last day of', $timeZone);
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use (&$currently) {
            return clone $currently;
        });
        $storage = new CM_Clockwork_Storage_Memory();
        $eventHandler = new CM_EventHandler_EventHandler();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance([$eventHandler]);
        $manager->setStorage($storage);
        $manager->setTimeZone($timeZone);
        $event = new CM_Clockwork_Event('event', 'first day of 09:00');
        $eventCounter = $this->_registerEvent($manager, $eventHandler, $event, function (CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) use ($manager) {
            $manager->setCompleted($event);
        });

        $manager->runEvents();
        $this->assertSame(0, $eventCounter->getCallCount());

        $currently->modify('next day 08:59:59');
        $manager->runEvents();
        $this->assertSame(0, $eventCounter->getCallCount());

        $currently->modify('09:00:00');
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());
    }

    public function testSchedulingFixedTimeMode_ExecutionDelayedIntoNextTimeframe() {
        $timeZone = CM_Bootloader::getInstance()->getTimeZone();
        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $currently = new DateTime('23:59', $timeZone);
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use (&$currently) {
            return clone $currently;
        });
        $storage = new CM_Clockwork_Storage_Memory();
        $eventHandler = new CM_EventHandler_EventHandler();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance([$eventHandler]);
        $manager->setStorage($storage);
        $manager->setTimeZone($timeZone);
        $event = new CM_Clockwork_Event('event', '23:59');
        $eventCounter = $this->_registerEvent($manager, $eventHandler, $event, function (CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) use ($manager) {
            $manager->setCompleted($event);
        });

        $currently->modify('next day 00:01');
        $this->assertSame(0, $eventCounter->getCallCount());
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());

        $currently->modify('23:58');
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());

        $currently->modify('23:59');
        $manager->runEvents();
        $this->assertSame(2, $eventCounter->getCallCount());
    }

    public function testSchedulingIntervalMode() {
        $timeZone = new DateTimeZone('UTC');
        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $currently = new DateTime('now', $timeZone);
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use (&$currently) {
            return clone $currently;
        });
        $storage = new CM_Clockwork_Storage_Memory();
        $eventHandler = new CM_EventHandler_EventHandler();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance([$eventHandler]);
        $manager->setStorage($storage);
        $manager->setTimeZone($timeZone);

        $event = new CM_Clockwork_Event('event', '2 seconds');
        $eventCounter = $this->_registerEvent($manager, $eventHandler, $event, function (CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) use ($manager) {
            $manager->setCompleted($event);
        });
        $manager->runEvents();
        $this->assertSame(0, $eventCounter->getCallCount());
        $currently->modify('1 seconds');
        $manager->runEvents();
        $this->assertSame(0, $eventCounter->getCallCount());
        $currently->modify('1 seconds');
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());
        $currently->modify('1 second');
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());
        $currently->modify('1 second');
        $manager->runEvents();
        $this->assertSame(2, $eventCounter->getCallCount());
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
        $currently = new DateTime('2014-10-26 00:10:00', new DateTimeZone('UTC'));

        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use (&$currently) {
            return clone $currently;
        });

        $eventHandler = new CM_EventHandler_EventHandler();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance([$eventHandler]);
        $manager->setTimeZone($timeZone);
        $event = new CM_Clockwork_Event('event', '02:10:00');
        $eventCounter = $this->_registerEvent($manager, $eventHandler, $event, function(CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) use ($manager) {
            $manager->setCompleted($event);
        });

        $manager->runEvents();
        $this->assertSame(0, $eventCounter->getCallCount());

        $currently->modify('01:09:59');
        $manager->runEvents();
        $this->assertSame(0, $eventCounter->getCallCount());

        $currently->modify('01:10:00');
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());

        $currently = new DateTime('2014-10-26 01:10:00', new DateTimeZone('UTC'));
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());

        $currently = new DateTime('2014-10-27 01:09:59', new DateTimeZone('UTC'));
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());
        $currently->modify('1 second');
        $manager->runEvents();
        $this->assertSame(2, $eventCounter->getCallCount());
    }

    public function testShouldRun_fixedTimeMode_forwardDaylightSavingTimeSwitch() {
        $timeZone = new DateTimeZone('Europe/Berlin'); // gmt +1/+2
        $currently = new DateTime('2014-03-30 01:09:59', new DateTimeZone('UTC'));

        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use (&$currently) {
            return clone $currently;
        });

        $eventHandler = new CM_EventHandler_EventHandler();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance([$eventHandler]);
        $manager->setTimeZone($timeZone);
        $event = new CM_Clockwork_Event('event', '02:10:00');
        $eventCounter = $this->_registerEvent($manager, $eventHandler, $event, function(CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) use ($manager) {
            $manager->setCompleted($event);
        });

        $manager->runEvents();
        $this->assertSame(0, $eventCounter->getCallCount());

        $currently->modify('1 second');
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());

        $currently = new DateTime('2014-03-31 00:09:59', new DateTimeZone('UTC'));
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());
        $currently->modify('1 second');
        $manager->runEvents();
        $this->assertSame(2, $eventCounter->getCallCount());
    }

    public function testShouldRun_fixedTimeMode_backwardDaylightSavingTimeSwitch_NegativeOffsetTimeZone() {
        $timeZone = new DateTimeZone('America/Chicago'); // gmt -6/-5
        $currently = new DateTime('2014-11-2 06:09:59', new DateTimeZone('UTC'));

        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use (&$currently) {
            return clone $currently;
        });

        $eventHandler = new CM_EventHandler_EventHandler();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance([$eventHandler]);
        $manager->setTimeZone($timeZone);
        $event = new CM_Clockwork_Event('event', '01:10:00');
        $eventCounter = $this->_registerEvent($manager, $eventHandler, $event, function(CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) use ($manager) {
            $manager->setCompleted($event);
        });

        $manager->runEvents();
        $this->assertSame(0, $eventCounter->getCallCount());
        $currently->modify('1 second');
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());

        $currently = new DateTime('2014-11-3 07:09:59', new DateTimeZone('UTC'));
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());
        $currently->modify('1 second');
        $manager->runEvents();
        $this->assertSame(2, $eventCounter->getCallCount());
    }

    public function testShouldRun_fixedTimeMode_forwardDaylightSavingTimeSwitch_NegativeOffsetTimeZone() {
        $timeZone = new DateTimeZone('America/Chicago'); // gmt -6/-5
        $currently = new DateTime('2014-03-09 07:10:00', new DateTimeZone('UTC'));

        $managerMock = $this->mockClass('CM_Clockwork_Manager');
        $managerMock->mockMethod('_getCurrentDateTimeUTC')->set(function () use (&$currently) {
            return clone $currently;
        });

        $eventHandler = new CM_EventHandler_EventHandler();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance([$eventHandler]);
        $storage = new CM_Clockwork_Storage_Memory();
        $manager->setStorage($storage);
        $manager->setTimeZone($timeZone);
        $event = new CM_Clockwork_Event('event', '02:10:00');
        $eventCounter = $this->_registerEvent($manager, $eventHandler, $event, function(CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) use ($manager) {
            $manager->setCompleted($event);
        });

        $manager->runEvents();
        $this->assertSame(0, $eventCounter->getCallCount());

        $currently->modify('08:09:59');
        $manager->runEvents();
        $this->assertSame(0, $eventCounter->getCallCount());

        $currently->modify('08:10:00');
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());

        $currently = new DateTime('2014-03-09 08:10:00', new DateTimeZone('UTC'));
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());

        $currently = new DateTime('2014-03-10 07:09:59', new DateTimeZone('UTC'));
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());
        $currently->modify('1 second');
        $manager->runEvents();
        $this->assertSame(2, $eventCounter->getCallCount());
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
        $storage = new CM_Clockwork_Storage_Memory();
        $eventHandler = new CM_EventHandler_EventHandler();
        /** @var CM_Clockwork_Manager $manager */
        $manager = $managerMock->newInstance([$eventHandler]);
        $manager->setStorage($storage);
        $manager->setTimeZone($timeZone);

        $event = new CM_Clockwork_Event('event', '20 minutes');
        $eventCounter = $this->_registerEvent($manager, $eventHandler, $event, function (CM_Clockwork_Event $event) use ($manager) {
            $manager->setCompleted($event);
        });

        $manager->runEvents();
        $this->assertSame(0, $eventCounter->getCallCount());
        $currently->modify('19 minutes 59 seconds');
        $manager->runEvents();
        $this->assertSame(0, $eventCounter->getCallCount());
        $currently->modify('1 second');
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());

        $currently->modify('19 minutes 59 seconds');
        $manager->runEvents();
        $this->assertSame(1, $eventCounter->getCallCount());
        $currently->modify('1 second');
        $manager->runEvents();
        $this->assertSame(2, $eventCounter->getCallCount());

        $currently->modify('19 minutes 59 seconds');
        $manager->runEvents();
        $this->assertSame(2, $eventCounter->getCallCount());
        $currently->modify('1 second');
        $manager->runEvents();
        $this->assertSame(3, $eventCounter->getCallCount());
        if ($timeZone->getOffset($start) === $timeZone->getOffset($currently)) {
            throw new CM_Exception_Invalid("Test did not go through a daylight saving time switch");
        }
    }

    /**
     * @param CM_Clockwork_Manager         $clockwork
     * @param CM_EventHandler_EventHandler $eventHandler
     * @param CM_Clockwork_Event           $event
     * @param Closure                      $callback
     * @return \Mocka\FunctionMock
     */
    protected function _registerEvent(CM_Clockwork_Manager $clockwork, CM_EventHandler_EventHandler $eventHandler, CM_Clockwork_Event $event, Closure $callback) {
        $functionMock = new \Mocka\FunctionMock();
        $functionMock->set($callback);
        $eventHandler->bind($event->getName(), function (CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) use ($callback, $functionMock) {
            $functionMock->invoke([$event, $status]);
        });
        $clockwork->registerEvent($event);
        return $functionMock;
    }
}
