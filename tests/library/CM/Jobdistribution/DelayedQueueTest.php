<?php

class CM_JobDistribution_DelayedQueueTest extends CMTest_TestCase {

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testQueueOutstanding() {
        /** @var CM_Gearman_Client|\Mocka\AbstractClassTrait $gearmanClient */
        $gearmanClient = $this->mockClass(CM_Gearman_Client::class)->newInstanceWithoutConstructor();
        $params1 = ['foo' => 12, 'bar' => 13];
        $params2 = ['foo' => 12, 'bar' => CMTest_TH::createUser()];
        /** @var \Mocka\FunctionMock $queueMethod */
        $queueMethod = $gearmanClient->mockMethod('queue')
            ->at(0, function (CM_Jobdistribution_Job_Abstract $job) use ($params2) {
                $this->assertEquals($params2, $job->getParams()->getParamsDecoded());
            })
            ->at(1, function (CM_Jobdistribution_Job_Abstract $job) use ($params1) {
                $this->assertEquals($params1, $job->getParams()->getParamsDecoded());
            });

        $queueMockClass = $this->mockInterface(CM_Jobdistribution_QueueInterface::class);
        $queueMockClass->mockMethod('queue')->set(function (CM_Jobdistribution_Job_Abstract $job) use ($gearmanClient) {
            $gearmanClient->queue($job);
        });
        $this->getServiceManager()->replaceInstance(CM_Jobdistribution_QueueInterface::class, $queueMockClass->newInstanceWithoutConstructor());

        $jobClassMock = $this->mockClass(CM_Jobdistribution_Job_Abstract::class);
        /** @var CM_Jobdistribution_Job_Abstract|\Mocka\AbstractClassTrait $jobMock1 */
        $jobMock1 = $jobClassMock->newInstance([CM_Params::factory($params1, false)]);
        /** @var CM_Jobdistribution_Job_Abstract|\Mocka\AbstractClassTrait $jobMock2 */
        $jobMock2 = $jobClassMock->newInstance([CM_Params::factory($params2, false)]);
        /** @var CM_Jobdistribution_Job_Abstract|\Mocka\AbstractClassTrait $jobMock3 */
        $jobMock3 = $jobClassMock->newInstance([CM_Params::factory([], false)]);

        /** @var CM_Jobdistribution_DelayedQueue|\Mocka\AbstractClassTrait $delayedQueue */
        $delayedQueue = $this->mockObject('CM_Jobdistribution_DelayedQueue', [$this->getServiceManager()]);
        /** @var \Mocka\FunctionMock $instantiateMethod */
        $instantiateMethod = $delayedQueue->mockMethod('_instantiateJob')
            ->at(0, function ($className) use ($jobMock2) {
                $this->assertSame($jobMock2->getJobName(), $className);
                return $jobMock2;
            })
            ->at(1, function ($className) use ($jobMock1) {
                $this->assertSame($jobMock1->getJobName(), $className);
                return $jobMock1;
            })
            ->at(2, null);

        $delayedQueue->addJob($jobMock2, 3);
        $delayedQueue->addJob($jobMock1, 2);
        $delayedQueue->addJob($jobMock3, 4);

        $delayedQueue->queueOutstanding();
        $this->assertSame(0, $queueMethod->getCallCount());

        CMTest_TH::timeForward(2);
        $delayedQueue->queueOutstanding();
        $this->assertSame(1, $queueMethod->getCallCount());

        CMTest_TH::timeForward(2);
        $delayedQueue->queueOutstanding();
        $this->assertSame(2, $queueMethod->getCallCount());

        $this->assertSame(3, $instantiateMethod->getCallCount());
    }

    public function testQueueOutstandingUndecodableParam() {
        $jobClassMock = $this->mockClass(CM_Jobdistribution_Job_Abstract::class);
        $delayedQueue = new CM_Jobdistribution_DelayedQueue($this->getServiceManager());
        $user = CMTest_TH::createUser();
        $jobParams = ['user' => $user];
        /** @var CM_Jobdistribution_Job_Abstract|\Mocka\AbstractClassTrait $jobMock */
        $jobMock = $jobClassMock->newInstance([CM_Params::factory($jobParams, false)]);
        $delayedQueue->addJob($jobMock, 0);
        $paramsEncoded = CM_Params::encode($jobParams, true);
        $user->delete();
        $logger = $this->mockObject(CM_Log_Logger::class);
        $this->getServiceManager()->replaceInstance('logger', $logger);

        $jobName = $jobMock->getJobName();
        $addMessageMock = $logger->mockMethod('addMessage')->set(function ($message, $level, CM_Log_Context $context = null) use ($jobName, $paramsEncoded) {
            $this->assertSame('Job-params could not be decoded', $message);
            $this->assertSame(CM_Log_Logger::WARNING, $level);
            $this->assertEquals(['job' => $jobName, 'paramsEncoded' => $paramsEncoded], $context->getExtra());
            $this->assertInstanceOf(CM_Exception_Nonexistent::class, $context->getException());
        });
        $delayedQueue->queueOutstanding();
        $this->assertSame(1, $addMessageMock->getCallCount());
    }

    public function testCancelJob() {
        $user = CMTest_TH::createUser();
        $paramsExec = ['foo' => 1, 'bar' => $user];
        $paramsCancel = ['foo' => 2, 'bar' => $user];

        $jobClassMock = $this->mockClass(CM_Jobdistribution_Job_Abstract::class);
        /** @var CM_Jobdistribution_Job_Abstract|\Mocka\AbstractClassTrait $job */
        $jobToExec = $jobClassMock->newInstance([CM_Params::factory($paramsExec, false)]);
        /** @var CM_Jobdistribution_Job_Abstract|\Mocka\AbstractClassTrait $job */
        $jobToCancel = $jobClassMock->newInstance([CM_Params::factory($paramsCancel, false)]);

        $queueMockClass = $this->mockInterface(CM_Jobdistribution_QueueInterface::class);
        $queueExecMethod = $queueMockClass->mockMethod('queue')->at(0, function (CM_Jobdistribution_Job_Abstract $job) use ($paramsExec) {
            $this->assertEquals($paramsExec, $job->getParams()->getParamsDecoded());
        });
        $this->getServiceManager()->replaceInstance(CM_Jobdistribution_QueueInterface::class, $queueMockClass->newInstanceWithoutConstructor());

        /** @var CM_Jobdistribution_DelayedQueue|\Mocka\AbstractClassTrait $delayedQueue */
        $delayedQueue = $this->mockObject(CM_Jobdistribution_DelayedQueue::class, [$this->getServiceManager()]);
        /** @var \Mocka\FunctionMock $instantiateMethod */
        $instantiateMethod = $delayedQueue->mockMethod('_instantiateJob')->set(function ($className, $paramsEncoded) use ($jobToExec, $jobToCancel, $paramsExec, $paramsCancel) {
            $job = null;
            if ($paramsEncoded === CM_Params::encode($paramsExec, true)) {
                $job = $jobToExec;
            } elseif ($paramsEncoded === CM_Params::encode($paramsCancel, true)) {
                $job = $jobToCancel;
            }
            $this->assertNotNull($job);
            return $job;
        });

        $delayedQueue->addJob($jobToExec, 2);
        $delayedQueue->addJob($jobToCancel, 2);

        CMTest_TH::timeForward(1);
        $delayedQueue->queueOutstanding();

        $this->assertSame(0, $instantiateMethod->getCallCount());
        $this->assertSame(0, $queueExecMethod->getCallCount());

        $delayedQueue->cancelJob($jobToCancel);

        CMTest_TH::timeForward(2);
        $delayedQueue->queueOutstanding();

        $this->assertSame(1, $instantiateMethod->getCallCount());
        $this->assertSame(1, $queueExecMethod->getCallCount());
    }

    public function testCountJob() {
        $this->_setupQueueMock();
        /** @var CM_Jobdistribution_DelayedQueue|\Mocka\AbstractClassTrait $delayedQueue */
        $delayedQueue = $this->mockObject(CM_Jobdistribution_DelayedQueue::class, [$this->getServiceManager()]);

        $jobClassMock = $this->mockClass(CM_Jobdistribution_Job_Abstract::class);
        $emptyJob = $jobClassMock->newInstance([CM_Params::factory([], false)]);
        $fooJob = $jobClassMock->newInstance([CM_Params::factory(['foo' => 1], false)]);
        $barJob = $jobClassMock->newInstance([CM_Params::factory(['bar' => 2], false)]);

        $this->assertSame(0, $delayedQueue->countJob($emptyJob));
        $this->assertSame(0, $delayedQueue->countJob($fooJob));

        $delayedQueue->addJob($emptyJob, 1);
        $this->assertSame(1, $delayedQueue->countJob($emptyJob));
        $this->assertSame(0, $delayedQueue->countJob($fooJob));

        $delayedQueue->addJob($fooJob, 1);
        $this->assertSame(1, $delayedQueue->countJob($emptyJob));
        $this->assertSame(1, $delayedQueue->countJob($fooJob));
        $this->assertSame(0, $delayedQueue->countJob($barJob));

        $delayedQueue->addJob($barJob, 1);
        $delayedQueue->addJob($barJob, 2);
        $this->assertSame(1, $delayedQueue->countJob($emptyJob));
        $this->assertSame(1, $delayedQueue->countJob($fooJob));
        $this->assertSame(2, $delayedQueue->countJob($barJob));

        CMTest_TH::timeForward(1);
        $delayedQueue->queueOutstanding();
        $this->assertSame(0, $delayedQueue->countJob($emptyJob));
        $this->assertSame(0, $delayedQueue->countJob($fooJob));
        $this->assertSame(1, $delayedQueue->countJob($barJob));
    }

    public function test_instantiateJobSetServiceManager() {
        /** @var CM_Jobdistribution_Job_Abstract|\Mocka\AbstractClassTrait|CM_Service_ManagerAwareInterface $job */
        $job = $this->mockClass(CM_Jobdistribution_Job_Abstract::class, ['CM_Service_ManagerAwareInterface'], ['CM_Service_ManagerAwareTrait'])->newInstanceWithoutConstructor();

        $queue = new CM_Jobdistribution_DelayedQueue($this->getServiceManager());
        $job = CMTest_TH::callProtectedMethod($queue, '_instantiateJob', [$job->getJobName(), CM_Params::encode(['foo' => 'bar'], true)]);
        $this->assertEquals($this->getServiceManager(), $job->getServiceManager());
    }

}
