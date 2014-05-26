<?php

class CM_Jobdistribution_Job_AbstractTest extends CMTest_TestCase {

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testRun() {
        if (!extension_loaded('gearman')) {
            $this->markTestSkipped('Gearman Pecl Extension not installed.');
        }
        CM_Config::get()->CM_Jobdistribution_Job_Abstract->gearmanEnabled = true;

        /** @var CM_Jobdistribution_Job_Abstract $job */
        $job = $this->getMockForAbstractClass('CM_Jobdistribution_Job_Abstract', array(), '', true, true, true,
            array('_getGearmanClient', '_execute'));
        $gearmanClientMock = $this->getMock('GearmanClient', array('doNormal', 'returnCode'));
        $that = $this;
        $gearmanJobMock = $this->getMock('GearmanJob', array('sendFail', 'workload'));
        $gearmanClientMock->expects($this->any())->method('doNormal')->will($this->returnCallback(function ($jobName, $workload) use ($job, $gearmanClientMock, $gearmanJobMock, $that) {
            $gearmanJobMock->expects($that->any())->method('workload')->will($that->returnValue($workload));
            $gearmanJobMock->expects($that->any())->method('sendFail')->will($that->returnCallback(function () use ($gearmanClientMock, $that) {
                $gearmanClientMock->expects($that->any())->method('returnCode')->will($that->returnValue(GEARMAN_WORK_FAIL));
            }));
            return $job->__executeGearman($gearmanJobMock);
        }));
        $job->expects($this->any())->method('_getGearmanClient')->will($this->returnValue($gearmanClientMock));
        $job->expects($this->any())->method('_execute')->will($this->returnCallback(function (CM_Params $params) {
            return array_flip($params->getAllOriginal());
        }));

        $result = $job->run(array('foo' => 'bar'));
        $this->assertSame(array('bar' => 'foo'), $result);

        // Exception thrown in worker
        $job->expects($this->any())->method('_execute')->will($this->returnCallback(function (CM_Params $params) use ($gearmanJobMock) {
            $gearmanJobMock->sendFail();
        }));
        try {
            $job->run(array('foo' => 'bar'));
            $this->fail('Job should have thrown an exception');
        } catch (CM_Exception $ex) {
            $this->assertContains('Job `' . get_class($job) . '` failed', $ex->getMessage());
        }
    }

    public function testRunMultiple() {
        if (!extension_loaded('gearman')) {
            $this->markTestSkipped('Gearman Pecl Extension not installed.');
        }
        CM_Config::get()->CM_Jobdistribution_Job_Abstract->gearmanEnabled = true;

        $gearmanClient = $this->getMock('GearmanClient', array('addTask', 'runTasks', 'setCompleteCallback', 'setFailCallback'));
        $gearmanClient->expects($this->exactly(2))->method('addTask')->will($this->returnValue(true));
        $gearmanClient->expects($this->exactly(1))->method('runTasks')->will($this->returnValue(true));
        $gearmanClient->expects($this->exactly(1))->method('setCompleteCallback')->will($this->returnCallback(function ($completeCallback) {
            $task1 = $this->getMockBuilder('GearmanTask')->setMethods(array('data'))->getMock();
            $task1->expects($this->once())->method('data')->will($this->returnValue(array('bar1' => 'foo1')));
            $completeCallback($task1);

            $task2 = $this->getMockBuilder('GearmanTask')->setMethods(array('data'))->getMock();
            $task2->expects($this->once())->method('data')->will($this->returnValue(array('bar2' => 'foo2')));
            $completeCallback($task2);
        }));
        $gearmanClient->expects($this->exactly(1))->method('setFailCallback');
        /** @var GearmanClient $gearmanClient */

        $job = $this->getMockBuilder('CM_Jobdistribution_Job_Abstract')->setMethods(array('_getGearmanClient'))->getMockForAbstractClass();
        $job->expects($this->any())->method('_getGearmanClient')->will($this->returnValue($gearmanClient));
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

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Job `myJob` failed (1/2 results)
     */
    public function testRunMultipleWithFailures() {
        if (!extension_loaded('gearman')) {
            $this->markTestSkipped('Gearman Pecl Extension not installed.');
        }
        CM_Config::get()->CM_Jobdistribution_Job_Abstract->gearmanEnabled = true;

        $gearmanClient = $this->getMock('GearmanClient', array('addTask', 'runTasks', 'setCompleteCallback', 'setFailCallback'));
        $gearmanClient->expects($this->exactly(2))->method('addTask')->will($this->returnValue(true));
        $gearmanClient->expects($this->exactly(1))->method('runTasks')->will($this->returnValue(true));
        $gearmanClient->expects($this->exactly(1))->method('setCompleteCallback')->will($this->returnCallback(function ($completeCallback) {
            $task1 = $this->getMockBuilder('GearmanTask')->setMethods(array('data'))->getMock();
            $task1->expects($this->once())->method('data')->will($this->returnValue(array('bar1' => 'foo1')));
            $completeCallback($task1);
        }));
        $gearmanClient->expects($this->exactly(1))->method('setFailCallback')->will($this->returnCallback(function ($failCallback) {
            $failCallback(new GearmanTask());
        }));
        /** @var GearmanClient $gearmanClient */

        $job = $this->getMockBuilder('CM_Jobdistribution_Job_Abstract')
            ->setMethods(array('_getGearmanClient', '_getJobName'))->getMockForAbstractClass();
        $job->expects($this->any())->method('_getGearmanClient')->will($this->returnValue($gearmanClient));
        $job->expects($this->any())->method('_getJobName')->will($this->returnValue('myJob'));
        /** @var CM_Jobdistribution_Job_Abstract $job */

        $job->runMultiple(array(
            array('foo1' => 'bar1'),
            array('foo2' => 'bar2'),
        ));
    }

    public function testRunGearmanDisabled() {
        CM_Config::get()->CM_Jobdistribution_Job_Abstract->gearmanEnabled = false;

        $job = $this->getMockForAbstractClass('CM_Jobdistribution_Job_Abstract', array(), '', true, true, true, array('_execute'));
        $job->expects($this->exactly(2))->method('_execute')->will($this->returnCallback(function (CM_Params $params) {
            return array_flip($params->getAllOriginal());
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
}
