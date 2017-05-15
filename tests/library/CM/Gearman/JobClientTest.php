<?php

class CM_Gearman_ClientTest extends CMTest_TestCase {

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testRunMultiple() {
        if (!extension_loaded('gearman')) {
            $this->markTestSkipped('Gearman Pecl Extension not installed.');
        }

        $mockBuilder = $this->getMockBuilder('GearmanClient');
        $mockBuilder->setMethods(['addTaskNormal', 'runTasks', 'setCompleteCallback', 'setFailCallback', 'addTaskHigh']);
        $gearmanClientMock = $mockBuilder->getMock();
        $gearmanClientMock->expects($this->exactly(1))->method('runTasks')->will($this->returnValue(true));
        $gearmanClientMock->expects($this->exactly(1))->method('setCompleteCallback')->will($this->returnCallback(function ($completeCallback) {
            $task1 = $this->getMockBuilder('GearmanTask')->setMethods(array('data'))->getMock();
            $task1->expects($this->once())->method('data')->will($this->returnValue(json_encode(array('bar1' => 'foo1'))));
            $completeCallback($task1);

            $task2 = $this->getMockBuilder('GearmanTask')->setMethods(array('data'))->getMock();
            $task2->expects($this->once())->method('data')->will($this->returnValue(json_encode(array('bar2' => 'foo2'))));
            $completeCallback($task2);
        }));
        $gearmanClientMock->expects($this->exactly(2))->method('addTaskHigh')->will($this->returnValue(true));
        /** @var GearmanClient $gearmanClientMock */
        $client = new CM_Gearman_Client($gearmanClientMock, (new CM_Gearman_Factory())->createSerializer());

        $jobMockClass = $this->mockClass(CM_Jobdistribution_Job_Abstract::class);
        $result = $client->runMultiple([
            $jobMockClass->newInstance([CM_Params::factory(['foo1' => 'bar1'], false)]),
            $jobMockClass->newInstance([CM_Params::factory(['foo2' => 'bar2'], false)]),
        ]);

        $this->assertSame([
            ['bar1' => 'foo1'],
            ['bar2' => 'foo2'],
        ], $result);
    }

    public function testQueuePriority() {
        if (!extension_loaded('gearman')) {
            $this->markTestSkipped('Gearman Pecl Extension not installed.');
        }
        $gearmanClientMock = $this->mockClass('GearmanClient')->newInstanceWithoutConstructor();

        $mockDoHighBackground = $gearmanClientMock->mockMethod('doHighBackground');
        $mockDoBackground = $gearmanClientMock->mockMethod('doBackground');
        $mockDoLowBackground = $gearmanClientMock->mockMethod('doLowBackground');

        $client = new CM_Gearman_Client($gearmanClientMock, (new CM_Gearman_Factory())->createSerializer());

        $jobClassMock = $this->mockClass(CM_Jobdistribution_Job_Abstract::class);
        // standard priority
        $client->queue($jobClassMock->newInstance([CM_Params::factory(['foo' => 'bar'], false)]));
        $this->assertSame(1, $mockDoBackground->getCallCount());

        // normal priority
        $jobClassMock->mockMethod('getPriority')->set(new CM_Jobdistribution_Priority('normal'));
        $client->queue($jobClassMock->newInstance([CM_Params::factory(['foo' => 'bar'], false)]));
        $this->assertSame(2, $mockDoBackground->getCallCount());

        // high priority
        $jobClassMock->mockMethod('getPriority')->set(new CM_Jobdistribution_Priority('high'));
        $client->queue($jobClassMock->newInstance([CM_Params::factory(['foo' => 'bar'], false)]));;
        $this->assertSame(1, $mockDoHighBackground->getCallCount());

        // low priority
        $jobClassMock->mockMethod('getPriority')->set(new CM_Jobdistribution_Priority('low'));
        $client->queue($jobClassMock->newInstance([CM_Params::factory(['foo' => 'bar'], false)]));
        $this->assertSame(1, $mockDoLowBackground->getCallCount());
    }

    public function testRunMultipleWithFailures() {
        if (!extension_loaded('gearman')) {
            $this->markTestSkipped('Gearman Pecl Extension not installed.');
        }

        $mockBuilder = $this->getMockBuilder('GearmanClient');
        $mockBuilder->setMethods(['addTaskHigh', 'runTasks', 'setCompleteCallback']);
        $gearmanClientMock = $mockBuilder->getMock();
        $gearmanClientMock->expects($this->exactly(2))->method('addTaskHigh')->will($this->returnValue(true));
        $gearmanClientMock->expects($this->exactly(1))->method('runTasks')->will($this->returnValue(true));
        $gearmanClientMock->expects($this->exactly(1))->method('setCompleteCallback')->will($this->returnCallback(function ($completeCallback) {
            $task1 = $this->getMockBuilder('GearmanTask')->setMethods(array('data'))->getMock();
            $task1->expects($this->once())->method('data')->will($this->returnValue(json_encode(array('bar1' => 'foo1'))));
            $completeCallback($task1);
        }));
        /** @var GearmanClient $gearmanClientMock */
        $client = new CM_Gearman_Client($gearmanClientMock, (new CM_Gearman_Factory())->createSerializer());
        $jobClassMock = $this->mockClass(CM_Jobdistribution_Job_Abstract::class);
        $jobClassMock->mockMethod('getJobName')->set('myJob');

        $exception = $this->catchException(function () use ($client, $jobClassMock) {
            $client->runMultiple([
                $jobClassMock->newInstance([CM_Params::factory(['foo1' => 'bar1'], false)]),
                $jobClassMock->newInstance([CM_Params::factory(['foo2' => 'bar2'], false)]),
            ]);
        });

        $this->assertInstanceOf('CM_Exception', $exception);
        /** @var CM_Exception $exception */
        $this->assertSame('Job failed. Invalid results', $exception->getMessage());
        $this->assertSame(
            [
                'jobNameList'     => [
                    'myJob', 'myJob',
                ],
                'countResultList' => 1,
                'countJobs'       => 2,
            ],
            $exception->getMetaInfo()
        );
    }

    public function testRun() {
        $client = $this->getMockBuilder(CM_Gearman_Client::class)->setMethods(array('runMultiple'))->disableOriginalConstructor()->getMock();
        $client->expects($this->once())->method('runMultiple')->will($this->returnCallback(function (array $paramsList) {
            return Functional\map($paramsList, function (CM_Jobdistribution_Job_Abstract $job) {
                return array_flip($job->getParams()->getParamsDecoded());
            });
        }));
        /** @var CM_Gearman_Client $client */
        $jobClassMock = $this->mockClass(CM_Jobdistribution_Job_Abstract::class);
        $result = $client->run($jobClassMock-> newInstance([CM_Params::factory(['foo' => 'bar'], false)]));
        $this->assertSame(array('bar' => 'foo'), $result);
    }
}
