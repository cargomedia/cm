<?php

class CM_JobDistribution_DelayedQueueTest extends CMTest_TestCase {

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testQueueOutstanding() {
        /** @var CM_Jobdistribution_Job_Abstract|\Mocka\AbstractClassTrait $job */
        $job = $this->mockObject('CM_Jobdistribution_Job_Abstract');
        $params1 = ['foo' => 12, 'bar' => 13];
        $params2 = ['foo' => 12, 'bar' => CMTest_TH::createUser()];
        /** @var \Mocka\FunctionMock $queueMethod */
        $queueMethod = $job->mockMethod('queue')
            ->at(0, function (array $params) use ($params1) {
                $this->assertEquals($params1, $params);
            })
            ->at(1, function (array $params) use ($params2) {
                $this->assertEquals($params2, $params);
            });

        /** @var CM_Jobdistribution_DelayedQueue|\Mocka\AbstractClassTrait $delayedQueue */
        $delayedQueue = $this->mockObject('CM_Jobdistribution_DelayedQueue', [$this->getServiceManager()]);
        /** @var \Mocka\FunctionMock $instantiateMethod */
        $instantiateMethod = $delayedQueue->mockMethod('_instantiateJob')
            ->at(0, function ($className) use ($job) {
                $this->assertSame(get_class($job), $className);
                return $job;
            })
            ->at(1, function ($className) use ($job) {
                $this->assertSame(get_class($job), $className);
                return $job;
            })
            ->at(2, null);

        $delayedQueue->addJob($job, $params2, 3);
        $delayedQueue->addJob($job, $params1, 2);
        $delayedQueue->addJob($job, [], 4);

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
        $job = $this->mockObject(CM_Jobdistribution_Job_Abstract::class);
        $delayedQueue = new CM_Jobdistribution_DelayedQueue($this->getServiceManager());
        $user = CMTest_TH::createUser();
        $jobParams = ['user' => $user];
        $delayedQueue->addJob($job, $jobParams, 0);
        $paramsEncoded = CM_Util::jsonEncode(CM_Params::encode($jobParams));
        $user->delete();
        $logger = $this->mockObject(CM_Log_Logger::class);
        $this->getServiceManager()->replaceInstance('logger', $logger);
        $jobName = get_class($job);

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
        /** @var CM_Jobdistribution_Job_Abstract|\Mocka\AbstractClassTrait $job */
        $jobToExec = $this->mockObject('CM_Jobdistribution_Job_Abstract');
        /** @var CM_Jobdistribution_Job_Abstract|\Mocka\AbstractClassTrait $job */
        $jobToCancel = $this->mockObject('CM_Jobdistribution_Job_Abstract');
        $user = CMTest_TH::createUser();
        $params1 = ['foo' => 1, 'bar' => $user];
        $params2 = ['foo' => 2, 'bar' => $user];

        $queueExecMethod = $jobToExec->mockMethod('queue')->set(function (array $params) use ($params1) {
            $this->assertEquals($params1, $params);
        });
        $queueCancelMethod = $jobToCancel->mockMethod('queue');
        /** @var CM_Jobdistribution_DelayedQueue|\Mocka\AbstractClassTrait $delayedQueue */
        $delayedQueue = $this->mockObject('CM_Jobdistribution_DelayedQueue', [$this->getServiceManager()]);
        /** @var \Mocka\FunctionMock $instantiateMethod */
        $instantiateMethod = $delayedQueue->mockMethod('_instantiateJob')->set(function ($className) use ($jobToExec, $jobToCancel) {
            $job = null;
            if ($className === get_class($jobToExec)) {
                $job = $jobToExec;
            } elseif ($className === get_class($jobToCancel)) {
                $job = $jobToCancel;
            }
            $this->assertNotNull($job);
            return $job;
        });

        $delayedQueue->addJob($jobToExec, $params1, 2);
        $delayedQueue->addJob($jobToCancel, $params2, 2);

        CMTest_TH::timeForward(1);
        $delayedQueue->queueOutstanding();

        $this->assertSame(0, $instantiateMethod->getCallCount());
        $this->assertSame(0, $queueExecMethod->getCallCount());
        $this->assertSame(0, $queueCancelMethod->getCallCount());

        $delayedQueue->cancelJob($jobToCancel, $params2);

        CMTest_TH::timeForward(2);
        $delayedQueue->queueOutstanding();

        $this->assertSame(1, $instantiateMethod->getCallCount());
        $this->assertSame(1, $queueExecMethod->getCallCount());
        $this->assertSame(0, $queueCancelMethod->getCallCount());
    }

    public function testCountJob() {
        /** @var CM_Jobdistribution_Job_Abstract|\Mocka\AbstractClassTrait $job */
        $job = $this->mockObject('CM_Jobdistribution_Job_Abstract');
        /** @var CM_Jobdistribution_DelayedQueue|\Mocka\AbstractClassTrait $delayedQueue */
        $delayedQueue = $this->mockObject('CM_Jobdistribution_DelayedQueue', [$this->getServiceManager()]);

        $this->assertSame(0, $delayedQueue->countJob($job, []));
        $this->assertSame(0, $delayedQueue->countJob($job, ['foo' => 1]));

        $delayedQueue->addJob($job, [], 1);
        $this->assertSame(1, $delayedQueue->countJob($job, []));
        $this->assertSame(0, $delayedQueue->countJob($job, ['foo' => 1]));

        $delayedQueue->addJob($job, ['foo' => 1], 1);
        $this->assertSame(1, $delayedQueue->countJob($job, []));
        $this->assertSame(1, $delayedQueue->countJob($job, ['foo' => 1]));
        $this->assertSame(0, $delayedQueue->countJob($job, ['foo' => 2]));

        $delayedQueue->addJob($job, ['foo' => 2], 1);
        $delayedQueue->addJob($job, ['foo' => 2], 2);
        $this->assertSame(1, $delayedQueue->countJob($job, []));
        $this->assertSame(1, $delayedQueue->countJob($job, ['foo' => 1]));
        $this->assertSame(2, $delayedQueue->countJob($job, ['foo' => 2]));

        CMTest_TH::timeForward(1);
        $delayedQueue->queueOutstanding();
        $this->assertSame(0, $delayedQueue->countJob($job, []));
        $this->assertSame(0, $delayedQueue->countJob($job, ['foo' => 1]));
        $this->assertSame(1, $delayedQueue->countJob($job, ['foo' => 2]));
    }

    public function test_instantiateJobSetServiceManager() {
        /** @var CM_Jobdistribution_Job_Abstract|\Mocka\AbstractClassTrait|CM_Service_ManagerAwareInterface $job */
        $job = $this->mockClass('CM_Jobdistribution_Job_Abstract', ['CM_Service_ManagerAwareInterface'], ['CM_Service_ManagerAwareTrait'])->newInstance();

        $queue = new CM_Jobdistribution_DelayedQueue($this->getServiceManager());
        $job = CMTest_TH::callProtectedMethod($queue, '_instantiateJob', [get_class($job)]);
        $this->assertEquals($this->getServiceManager(), $job->getServiceManager());
    }

}
