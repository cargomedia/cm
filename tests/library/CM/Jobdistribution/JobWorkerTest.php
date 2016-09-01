<?php

class CM_JobDistribution_JobWorkerTest extends CMTest_TestCase {

    public function testRun() {
        if (!extension_loaded('gearman')) {
            $this->markTestSkipped('Gearman Pecl Extension not installed.');
        }
        $counter = 0;
        $gearmanWorkerMock = $this->getMock('GearmanWorker', array('work'));
        $gearmanWorkerMock->expects($this->exactly(2))->method('work')->will($this->returnCallback(function () use (&$counter) {
            if (++$counter >= 2) {
                return false;
            }
            throw new Exception('foo-bar');
        }));
        $jobWorkerMock = $this->getMock('CM_Jobdistribution_JobWorker', array('_getGearmanWorker', '_handleException'), array(), '', false);
        $jobWorkerMock->expects($this->any())->method('_getGearmanWorker')->will($this->returnValue($gearmanWorkerMock));
        /** @var CM_JobDistribution_JobWorker $jobWorkerMock */
        $serviceManager = new CM_Service_Manager();
        $jobWorkerMock->setServiceManager($serviceManager);
        /** @var CM_Log_Logger|\Mocka\AbstractClassTrait $logger */
        $logger = $this->mockObject('CM_Log_Logger');
        $serviceManager->unregister('logger')->registerInstance('logger', $logger);
        $logExceptionMock = $logger->mockMethod('logException')->set(function (Exception $exception, $level = null) {
            $this->assertEquals('foo-bar', $exception->getMessage());
            $this->assertEquals(null, $level);
        });
        try {
            $jobWorkerMock->run();
        } catch (CM_Exception_Invalid $ex) {
            $this->assertContains('Worker failed', $ex->getMessage());
            $this->assertSame(2, $counter);
            $this->assertSame(1, $logExceptionMock->getCallCount());
        } catch (Exception $ex) {
            $this->fail('Exception not caught.');
        }
    }
}
