<?php

class CM_JobDistribution_JobWorkerTest extends CMTest_TestCase {

    public function testRun() {
        if (!extension_loaded('gearman')) {
            $this->markTestSkipped('Gearman Pecl Extension not installed.');
        }
        $gearmanWorker = $this->mockObject('GearmanWorker');
        $gearmanWorker->mockMethod('work')
            ->set(false)
            ->at(0, function() {
                throw new Exception('foo-bar');
            });
        $jobWorker = $this->mockClass('CM_Jobdistribution_JobWorker')->newInstanceWithoutConstructor();
        $jobWorker->mockMethod('_getGearmanWorker')->set($gearmanWorker);
        $jobWorker->mockMethod('_handleException')->set(function(Exception $exception) {
            $this->assertSame('foo-bar', $exception->getMessage());
        });
        /** @var CM_JobDistribution_JobWorker $jobWorker */
        try {
            $jobWorker->run();
        } catch (CM_Exception_Invalid $ex) {
            $this->assertContains('Worker failed', $ex->getMessage());
            $this->assertSame(2, $gearmanWorker->mockMethod('work')->getCallCount());
            $this->assertSame(1, $jobWorker->mockMethod('_handleException')->getCallCount());
        } catch (Exception $ex) {
            $this->fail('Exception not caught.');
        }
    }
}
