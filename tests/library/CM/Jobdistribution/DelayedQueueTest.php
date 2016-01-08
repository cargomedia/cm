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

        $delayedQueue->addJob($job, $params2, time() + 3);
        $delayedQueue->addJob($job, $params1, time() + 2);
        $delayedQueue->addJob($job, [], time() + 4);

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
}
