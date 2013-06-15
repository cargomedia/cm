<?php

class CM_Jobdistribution_Job_AbstractTest extends CMTest_TestCase {

	public function testRun() {
		if (!extension_loaded('gearman')) {
			$this->markTestSkipped('Gearman Pecl Extension not installed.');
		}
		CM_Config::get()->CM_Jobdistribution_Job_Abstract->gearmanEnabled = true;

		/** @var CM_Jobdistribution_Job_Abstract $job  */
		$job = $this->getMockForAbstractClass('CM_Jobdistribution_Job_Abstract', array(), '', true, true, true, array('_getGearmanClient', '_execute'));
		$gearmanClientMock = $this->getMock('GearmanClient', array('doNormal', 'returnCode'));
		$that = $this;
		$gearmanJobMock = $gearmanJobMock = $this->getMock('GearmanJob', array('sendFail', 'workload'));
		$gearmanClientMock->expects($this->any())->method('doNormal')->will($this->returnCallback(function($jobName, $workload) use ($job, $gearmanClientMock, &$gearmanJobMock, $that) {
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

		CMTest_TH::clearConfig();
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

		CMTest_TH::clearConfig();
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

		CMTest_TH::clearConfig();
	}

}
