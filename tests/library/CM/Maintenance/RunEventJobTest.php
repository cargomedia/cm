<?php

class CM_Maintenance_RunEventJobTest extends CMTest_TestCase {

    public function testExecute() {
        $serviceManager = new CM_Service_Manager();
        $job = new CM_Maintenance_RunEventJob();
        $job->setServiceManager($serviceManager);

        /** @var CM_Maintenance_Service|\Mocka\AbstractClassTrait $maintenance */
        $maintenance = $this->mockClass(CM_Maintenance_Service::class)->newInstanceWithoutConstructor();
        $mockHandleClockworkEventResult = $maintenance->mockMethod('handleClockworkEventResult')
            ->at(0, function ($eventName, CM_Clockwork_Event_Result $result) {
                $this->assertSame('foo', $eventName);
                $this->assertSame(true, $result->isSuccessful());
            })
            ->at(1, function ($eventName, CM_Clockwork_Event_Result $result) {
                $this->assertSame('bar', $eventName);
                $this->assertSame(false, $result->isSuccessful());
            });
        $serviceManager->replaceInstance('maintenance', $maintenance);

        $fooCounter = 0;
        $maintenance->registerEvent('foo', '1 second', function () use (&$fooCounter) {
            $fooCounter++;
        });
        $maintenance->registerEvent('bar', '1 second', function () {
            throw new Exception('Foo');
        });

        $this->assertSame(0, $fooCounter);
        $this->assertSame(0, $mockHandleClockworkEventResult->getCallCount());
        $job->run(['event' => 'foo', 'lastRuntime' => null]);
        $this->assertSame(1, $fooCounter);
        $this->assertSame(1, $mockHandleClockworkEventResult->getCallCount());

        $exception = $this->catchException(function () use ($job) {
            $job->run(['event' => 'bar', 'lastRuntime' => null]);
        });
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertSame('Foo', $exception->getMessage());
        $this->assertSame(2, $mockHandleClockworkEventResult->getCallCount());
    }
}
