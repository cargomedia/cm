<?php

class CM_Gearman_WorkerTest extends CMTest_TestCase {

    public function testRun() {
        if (!extension_loaded('gearman')) {
            $this->markTestSkipped('Gearman Pecl Extension not installed.');
        }
        $counter = 0;
        $mockBuilder = $this->getMockBuilder('GearmanWorker');
        $mockBuilder->setMethods(['work']);
        $gearmanWorkerMock = $mockBuilder->getMock();
        $gearmanWorkerMock->expects($this->exactly(2))->method('work')->will($this->returnCallback(function () use (&$counter) {
            if (++$counter >= 2) {
                return false;
            }
            throw new Exception('foo-bar');
        }));
        $jobWorker = new CM_Gearman_Worker($gearmanWorkerMock, (new CM_Gearman_Factory())->createSerializer(), 1000);
        /** @var CM_Gearman_Worker $jobWorker */
        $serviceManager = new CM_Service_Manager();
        $jobWorker->setServiceManager($serviceManager);
        /** @var CM_Log_Logger|\Mocka\AbstractClassTrait $logger */
        $logger = $this->mockObject('CM_Log_Logger');
        $serviceManager->registerInstance('logger', $logger);
        $addMessageMock = $logger->mockMethod('addMessage')->set(function ($message, $level, CM_Log_Context $context = null) {
            $this->assertSame('Worker failed', $message);
            $this->assertEquals(CM_Log_Logger::ERROR, $level);
            $exception = $context->getException();
            $this->assertInstanceOf('Exception', $exception);
            $this->assertEquals('foo-bar', $exception->getMessage());
        });
        try {
            $jobWorker->run();
        } catch (CM_Exception_Invalid $ex) {
            $this->assertContains('Worker failed', $ex->getMessage());
            $this->assertSame(2, $counter);
            $this->assertSame(1, $addMessageMock->getCallCount());
        } catch (Exception $ex) {
            $this->fail('Exception not caught.');
        }
    }

    public function testRunJobLimit() {
        if (!extension_loaded('gearman')) {
            $this->markTestSkipped('Gearman Pecl Extension not installed.');
        }
        $serviceManager = new CM_Service_Manager();
        $logger = $this->mockObject('CM_Log_Logger');
        $serviceManager->registerInstance('logger', $logger);

        $gearmanWorker = $this->mockClass('GearmanWorker')->newInstanceWithoutConstructor();
        $workMethod = $gearmanWorker->mockMethod('work')->set(true);

        $jobWorker = new CM_Gearman_Worker($gearmanWorker, (new CM_Gearman_Factory())->createSerializer(), 5);
        $jobWorker->setServiceManager($serviceManager);
        $jobWorker->run();
        $this->assertSame(5, $workMethod->getCallCount());
    }
}
