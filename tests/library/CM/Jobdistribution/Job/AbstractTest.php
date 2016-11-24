<?php

class CM_Jobdistribution_Job_AbstractTest extends CMTest_TestCase {

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testRunMultiple() {
        if (!extension_loaded('gearman')) {
            $this->markTestSkipped('Gearman Pecl Extension not installed.');
        }
        CM_Config::get()->CM_Jobdistribution_Job_Abstract->gearmanEnabled = true;

        $mockBuilder = $this->getMockBuilder('GearmanClient');
        $mockBuilder->setMethods(['addTaskNormal', 'runTasks', 'setCompleteCallback', 'setFailCallback']);
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
        $gearmanClientMock->expects($this->exactly(1))->method('setFailCallback');
        /** @var GearmanClient $gearmanClientMock */

        $job = $this->getMockBuilder('CM_Jobdistribution_Job_Abstract')->setMethods(array('_getGearmanClient', '_addTask'))->getMockForAbstractClass();
        $job->expects($this->any())->method('_getGearmanClient')->will($this->returnValue($gearmanClientMock));
        $job->expects($this->exactly(2))->method('_addTask')->will($this->returnValue(true));
        /** @var CM_Jobdistribution_Job_Abstract $job */

        $result = $job->runMultiple(array(
            array('foo1' => 'bar1'),
            array('foo2' => 'bar2'),
        ));

        $this->assertSame(array(
            array('bar1' => 'foo1'),
            array('bar2' => 'foo2'),
        ), $result);
    }

    public function testQueuePriority() {
        if (!extension_loaded('gearman')) {
            $this->markTestSkipped('Gearman Pecl Extension not installed.');
        }
        CM_Config::get()->CM_Jobdistribution_Job_Abstract->gearmanEnabled = true;

        $gearmanClient = $this->mockClass('GearmanClient')->newInstanceWithoutConstructor();

        $mockDoHighBackground = $gearmanClient->mockMethod('doHighBackground');
        $mockDoBackground = $gearmanClient->mockMethod('doBackground');
        $mockDoLowBackground = $gearmanClient->mockMethod('doLowBackground');

        /** @var CM_Jobdistribution_Job_Abstract|\Mocka\AbstractClassTrait $job */
        $job = $this->mockObject('CM_Jobdistribution_Job_Abstract');
        $job->mockMethod('_getGearmanClient')->set($gearmanClient);

        // standard priority
        $job->queue([['foo' => 'bar']]);
        $this->assertSame(1, $mockDoBackground->getCallCount());

        // normal priority
        $priorityMock = $job->mockMethod('getPriority');
        $priorityMock->set(new CM_Jobdistribution_Priority('normal'));
        $job->queue([['foo' => 'bar']]);
        $this->assertSame(2, $mockDoBackground->getCallCount());

        // high priority
        $priorityMock = $job->mockMethod('getPriority');
        $priorityMock->set(new CM_Jobdistribution_Priority('high'));
        $job->queue([['foo' => 'bar']]);
        $this->assertSame(1, $mockDoHighBackground->getCallCount());

        // low priority
        $priorityMock = $job->mockMethod('getPriority');
        $priorityMock->set(new CM_Jobdistribution_Priority('low'));
        $job->queue([['foo' => 'bar']]);
        $this->assertSame(1, $mockDoLowBackground->getCallCount());
    }

    public function testRunMultipleWithFailures() {
        if (!extension_loaded('gearman')) {
            $this->markTestSkipped('Gearman Pecl Extension not installed.');
        }
        CM_Config::get()->CM_Jobdistribution_Job_Abstract->gearmanEnabled = true;

        $mockBuilder = $this->getMockBuilder('GearmanClient');
        $mockBuilder->setMethods(['addTaskHigh', 'runTasks', 'setCompleteCallback', 'setFailCallback']);
        $gearmanClientMock = $mockBuilder->getMock();
        $gearmanClientMock->expects($this->exactly(2))->method('addTaskHigh')->will($this->returnValue(true));
        $gearmanClientMock->expects($this->exactly(1))->method('runTasks')->will($this->returnValue(true));
        $gearmanClientMock->expects($this->exactly(1))->method('setCompleteCallback')->will($this->returnCallback(function ($completeCallback) {
            $task1 = $this->getMockBuilder('GearmanTask')->setMethods(array('data'))->getMock();
            $task1->expects($this->once())->method('data')->will($this->returnValue(json_encode(array('bar1' => 'foo1'))));
            $completeCallback($task1);
        }));
        $gearmanClientMock->expects($this->exactly(1))->method('setFailCallback')->will($this->returnCallback(function ($failCallback) {
            $failCallback(new GearmanTask());
        }));
        /** @var GearmanClient $gearmanClientMock */

        $job = $this->getMockBuilder('CM_Jobdistribution_Job_Abstract')
            ->setMethods(array('_getGearmanClient', '_getJobName'))->getMockForAbstractClass();
        $job->expects($this->any())->method('_getGearmanClient')->will($this->returnValue($gearmanClientMock));
        $job->expects($this->any())->method('_getJobName')->will($this->returnValue('myJob'));
        /** @var CM_Jobdistribution_Job_Abstract $job */

        $exception = $this->catchException(function () use ($job) {
            $job->runMultiple(array(
                array('foo1' => 'bar1'),
                array('foo2' => 'bar2'),
            ));
        });

        $this->assertInstanceOf('CM_Exception', $exception);
        /** @var CM_Exception $exception */
        $this->assertSame('Job failed. Invalid results', $exception->getMessage());
        $this->assertSame(
            [
                'jobName'         => 'myJob',
                'countResultList' => 1,
                'countParamList'  => 2,
            ],
            $exception->getMetaInfo()
        );
    }

    public function testRun() {
        $job = $this->getMockBuilder('CM_Jobdistribution_Job_Abstract')->setMethods(array('runMultiple'))->getMockForAbstractClass();
        $job->expects($this->once())->method('runMultiple')->will($this->returnCallback(function (array $paramsList) {
            return Functional\map($paramsList, function ($params) {
                return array_flip($params);
            });
        }));
        /** @var CM_Jobdistribution_Job_Abstract $job */

        $result = $job->run(array('foo' => 'bar'));
        $this->assertSame(array('bar' => 'foo'), $result);
    }

    public function testRunGearmanDisabled() {
        CM_Config::get()->CM_Jobdistribution_Job_Abstract->gearmanEnabled = false;

        $job = $this->getMockForAbstractClass('CM_Jobdistribution_Job_Abstract', array(), '', true, true, true, array('_execute'));
        $job->expects($this->exactly(2))->method('_execute')->will($this->returnCallback(function (CM_Params $params) {
            return array_flip($params->getParamsDecoded());
        }));

        /** @var CM_Jobdistribution_Job_Abstract $job */
        $result = $job->run(array('foo' => 'bar'));
        $this->assertSame(array('bar' => 'foo'), $result);

        $job->queue(array('foo' => 'bar'));
    }

    public function testRunGearmanDisabledThrows() {
        CM_Config::get()->CM_Jobdistribution_Job_Abstract->gearmanEnabled = false;

        $job = $this->getMockForAbstractClass('CM_Jobdistribution_Job_Abstract', array(), '', true, true, true, array('_execute'));
        $job->expects($this->exactly(1))->method('_execute')->will($this->returnCallback(function (CM_Params $params) {
            throw new Exception('Job failed');
        }));

        /** @var CM_Jobdistribution_Job_Abstract $job */
        try {
            $job->run(array('foo' => 'bar'));
            $this->fail('Job should have thrown an exception');
        } catch (Exception $ex) {
            $this->assertSame('Job failed', $ex->getMessage());
        }
    }

    public function testVerifyParamsThrows() {
        $job = $this->getMockForAbstractClass('CM_Jobdistribution_Job_Abstract');

        /** @var CM_Jobdistribution_Job_Abstract $job */
        try {
            $job->run(array('foo' => 'foo', 'bar' => new stdClass()));
            $this->fail('Job should have thrown an exception');
        } catch (CM_Exception_InvalidParam $ex) {
            $this->assertSame('Object is not an instance of CM_ArrayConvertible', $ex->getMessage());
            $this->assertSame(['className' => 'stdClass'], $ex->getMetaInfo());
        }

        /** @var CM_Jobdistribution_Job_Abstract $job */
        try {
            $job->queue(array('foo' => 'foo', 'bar' => ['bar' => new stdClass()]));
            $this->fail('Job should have thrown an exception');
        } catch (CM_Exception_InvalidParam $ex) {
            $this->assertSame('Object is not an instance of CM_ArrayConvertible', $ex->getMessage());
            $this->assertSame(['className' => 'stdClass'], $ex->getMetaInfo());
        }
    }
}
