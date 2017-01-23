<?php

class CM_JobDistribution_JobWorkerTest extends CMTest_TestCase {

    public function testRun() {
        CM_Config::get()->CM_Jobdistribution_JobWorker->servers = [];
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
        $mockBuilder = $this->getMockBuilder('CM_Jobdistribution_JobWorker')->setConstructorArgs([1000]);
        $mockBuilder->setMethods(['_getGearmanWorker', '_handleException']);
        $jobWorkerMock = $mockBuilder->getMock();
        $jobWorkerMock->expects($this->any())->method('_getGearmanWorker')->will($this->returnValue($gearmanWorkerMock));
        /** @var CM_Gearman_JobWorker $jobWorkerMock */
        $serviceManager = new CM_Service_Manager();
        $jobWorkerMock->setServiceManager($serviceManager);
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
            $jobWorkerMock->run();
        } catch (CM_Exception_Invalid $ex) {
            $this->assertContains('Worker failed', $ex->getMessage());
            $this->assertSame(2, $counter);
            $this->assertSame(1, $addMessageMock->getCallCount());
        } catch (Exception $ex) {
            $this->fail('Exception not caught.');
        }
    }

    public function testRunJobLimit() {
        $serviceManager = new CM_Service_Manager();
        $logger = $this->mockObject('CM_Log_Logger');
        $serviceManager->registerInstance('logger', $logger);
        
        if (!extension_loaded('gearman')) {
            $this->markTestSkipped('Gearman Pecl Extension not installed.');
        }
        $gearmanWorker = $this->mockClass('GearmanWorker')->newInstanceWithoutConstructor();
        $workMethod = $gearmanWorker->mockMethod('work')->set(true);

        CM_Config::get()->CM_Jobdistribution_JobWorker->servers = [];
        $worker = $this->mockClass(CM_Gearman_JobWorker::class)->newInstance([5]);
        $worker->mockMethod('_getGearmanWorker')->set($gearmanWorker);
        /** @var CM_Gearman_JobWorker $worker */
        $worker->setServiceManager($serviceManager);
        $worker->run();
        $this->assertSame(5, $workMethod->getCallCount());
    }
}
