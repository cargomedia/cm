<?php

class CM_Maintenance_ServiceTest extends CMTest_TestCase {

    public function testHandleClockworkEventResult() {
        $clockworkManager = $this->mockClass('CM_Clockwork_Manager')->newInstanceWithoutConstructor();
        $mockHandleEventResult = $clockworkManager->mockMethod('handleEventResult');
        /** @var CM_Maintenance_Service|\Mocka\AbstractClassTrait $maintenance */
        $maintenance = $this->mockClass(CM_Maintenance_Service::class)->newInstanceWithoutConstructor();
        $maintenance->mockMethod('_getClockworkManager')->set($clockworkManager);
        $maintenance->registerEvent('foo', '1 second', function () {
        });

        $result = new CM_Clockwork_Event_Result();
        $mockHandleEventResult->set(function (CM_Clockwork_Event $event, CM_Clockwork_Event_Result $result) use ($result) {
            $this->assertSame('foo', $event->getName());
            $this->assertSame('1 second', $event->getDateTimeString());
            $this->assertSame($result, $result);
        });

        $this->assertSame(0, $mockHandleEventResult->getCallCount());
        $maintenance->handleClockworkEventResult('foo', $result);
        $this->assertSame(1, $mockHandleEventResult->getCallCount());

        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($maintenance, $result) {
            $maintenance->handleClockworkEventResult('bar', $result);
        });
        $this->assertInstanceOf(CM_Exception_Invalid::class, $exception);
        $this->assertSame('Event not found', $exception->getMessage());
        $this->assertSame(['event' => 'bar'], $exception->getMetaInfo());
    }

    public function testRegisterEvent() {
        $clockworkManager = $this->mockClass('CM_Clockwork_Manager')->newInstanceWithoutConstructor();
        /** @var CM_Maintenance_Service|\Mocka\AbstractClassTrait $maintenance */
        $maintenance = $this->mockClass(CM_Maintenance_Service::class)->newInstanceWithoutConstructor();
        $maintenance->mockMethod('_getClockworkManager')->set($clockworkManager);
        $maintenance->registerEvent('foo', '1 second', function () {
        });
        $maintenance->registerEvent('bar', '1 second', function () {
        });
        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($maintenance) {
            $maintenance->registerEvent('foo', '1 second', function () {
            });
        });
        $this->assertInstanceOf(CM_Exception_Invalid::class, $exception);
        $this->assertSame('Duplicate event-name', $exception->getMessage());
        $this->assertSame(['event' => 'foo'], $exception->getMetaInfo());
    }

    public function testRunEvent() {
        $clockworkManager = $this->mockClass('CM_Clockwork_Manager')->newInstanceWithoutConstructor();
        /** @var CM_Maintenance_Service|\Mocka\AbstractClassTrait $maintenance */
        $maintenance = $this->mockClass(CM_Maintenance_Service::class)->newInstanceWithoutConstructor();
        $maintenance->mockMethod('_getClockworkManager')->set($clockworkManager);
        $fooCounter = 0;
        $maintenance->registerEvent('foo', '1 second', function () use (&$fooCounter) {
            $fooCounter++;
        });

        $this->assertSame(0, $fooCounter);
        $maintenance->runEvent('foo');
        $this->assertSame(1, $fooCounter);

        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($maintenance) {
            $maintenance->runEvent('bar');
        });
        $this->assertInstanceOf(CM_Exception_Invalid::class, $exception);
        $this->assertSame('Event not found', $exception->getMessage());
        $this->assertSame(['event' => 'bar'], $exception->getMetaInfo());
    }

    public function testRunEvents() {
        $clockworkManager = $this->mockClass('CM_Clockwork_Manager')->newInstanceWithoutConstructor();
        $mockRunEvents = $clockworkManager->mockMethod('runEvents');
        /** @var CM_Maintenance_Service|\Mocka\AbstractClassTrait $maintenance */
        $maintenance = $this->mockClass(CM_Maintenance_Service::class)->newInstanceWithoutConstructor();
        $maintenance->mockMethod('_getClockworkManager')->set($clockworkManager);

        $this->assertSame(0, $mockRunEvents->getCallCount());
        $maintenance->runEvents();
        $this->assertSame(1, $mockRunEvents->getCallCount());
    }

    public function testMaintenance() {
        $maintenance = new CM_Maintenance_Service();
        $serviceManager = new CM_Service_Manager();
        $serviceManager->replaceInstance('maintenance', $maintenance);
        $maintenance->setServiceManager($serviceManager);

        $fooCounter = 0;
        $barCounter = 0;
        $maintenance->registerEvent('foo', '1 second', function () use (&$fooCounter) {
            $fooCounter++;
        });
        $maintenance->registerEvent('bar', '2 seconds', function () use (&$barCounter) {
            $barCounter++;
        });

        $maintenance->runEvents();
        $this->assertSame(0, $fooCounter);
        $this->assertSame(0, $barCounter);

        CMTest_TH::timeForward(1);
        $maintenance->runEvents();
        $this->assertSame(1, $fooCounter);
        $this->assertSame(0, $barCounter);

        CMTest_TH::timeForward(1);
        $maintenance->runEvents();
        $this->assertSame(2, $fooCounter);
        $this->assertSame(1, $barCounter);
    }
}
