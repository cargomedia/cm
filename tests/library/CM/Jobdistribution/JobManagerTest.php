<?php

class CM_Jobdistribution_JobManagerTest extends CMTest_TestCase {

    public function testRun() {
        /** @var CM_Jobdistribution_Job_Abstract $job */
        $job = $this->mockObject('CM_Jobdistribution_Job_Abstract');

        $jobManager = $this->mockObject('CM_Jobdistribution_JobManager');
        $runMultipleMethod = $jobManager->mockMethod('runMultiple')->set(function ($jobs) use ($job) {
            $this->assertSame([$job], $jobs);
            return ['result'];
        });

        /** @var CM_Jobdistribution_JobManager $jobManager */
        $this->assertSame('result', $jobManager->run($job));
        $this->assertSame(1, $runMultipleMethod->getCallCount());
    }

    public function testRunMultipleWithoutGearman() {
        $jobManager = new CM_Jobdistribution_JobManager();
        $jobManager->setServiceManager(new CM_Service_Manager());
        $job = $this->mockObject('CM_Jobdistribution_Job_Abstract');
        $executeMethod = $job->mockMethod('execute')->set(function () {
            return 'foo';
        });
        $this->assertSame(['foo', 'foo'], $jobManager->runMultiple([$job, $job]));
        $this->assertSame(2, $executeMethod->getCallCount());
    }

    public function testRunMultiple() {
        $jobManager = new CM_Jobdistribution_JobManager(array(['host' => 'localhost', 'port' => 4730]));
        $jobManager->setServiceManager(new CM_Service_Manager());
        $jobClass = $this->mockClass('CM_Jobdistribution_Job_Abstract');
        $jobClass->mockMethod('execute')->set(function () {
            return 'foo';
        });
        /** @var CM_Jobdistribution_Job_Abstract $job */
        $job = $jobClass->newInstance();

        $process = CM_Process::getInstance();
        $process->fork(function () use ($jobManager, $job) {
            $worker = $jobManager->getWorker();
            $worker->registerJob($job->getJobName());
            $worker->run();
        });

        $result = $jobManager->runMultiple([$job]);
        $this->assertSame(['foo'], $result);
        $process->killChildren();
    }

    public function testQueue() {
        $jobManager = new CM_Jobdistribution_JobManager(array(['host' => 'localhost', 'port' => 4730]));
        $jobManager->setServiceManager(new CM_Service_Manager());
        $jobClass = $this->mockClass('CM_Jobdistribution_Job_Abstract');
        $file = CM_File::createTmp();
        $jobClass->mockMethod('execute')->set(function () use ($file) {
            $file->write('foo');
        });
        /** @var CM_Jobdistribution_Job_Abstract $job */
        $job = $jobClass->newInstance();

        $process = CM_Process::getInstance();
        $process->fork(function () use ($jobManager, $job) {
            $worker = $jobManager->getWorker();
            $worker->registerJob($job->getJobName());
            $worker->run();
        });

        $jobManager->queue($job);
        sleep(1);
        $this->assertSame('foo', $file->read());
        $process->killChildren();
    }
}
