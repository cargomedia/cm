<?php
require_once __DIR__ . '/../../../../TestCase.php';


class CM_Jobdistribution_Job_AbstractTest extends TestCase {

	public function testRun() {
		if (!extension_loaded('gearman')) {
			$this->markTestSkipped('Gearman Pecl Extension not installed.');
		}
		CM_Config::get()->CM_Jobdistribution_Job_Abstract->gearmanEnabled = true;

		/** @var CM_Jobdistribution_Job_Abstract $job  */
		$job = $this->getMockForAbstractClass('CM_Jobdistribution_Job_Abstract', array(), '', true, true, true, array('_getGearmanClient', '_run'));
		$gearmanClientMock = $this->getMock('GearmanClient', array('doNormal', 'returnCode'));
		$that = $this;
		$gearmanClientMock->expects($this->any())->method('doNormal')->will($this->returnCallback(function($jobName, $workload) use ($job, $gearmanClientMock, $that) {
			$gearmanJobMock = $that->getMock('GearmanJob', array('sendFail', 'workload'));
			$gearmanJobMock->expects($that->any())->method('workload')->will($that->returnValue($workload));
			$gearmanJobMock->expects($that->any())->method('sendFail')->will($that->returnCallback(function () use ($gearmanClientMock, $that) {
				$gearmanClientMock->expects($that->any())->method('returnCode')->will($that->returnValue(GEARMAN_WORK_FAIL));
			}));
			return $job->__run($gearmanJobMock);
		}));
		$job->expects($this->any())->method('_getGearmanClient')->will($this->returnValue($gearmanClientMock));
		$job->expects($this->any())->method('_run')->will($this->returnCallback(function (CM_Params $params) {
			return array_flip($params->getAllOriginal());
		}));

		$result = $job->run(array('foo' => 'bar'));
		$this->assertSame(array('bar' => 'foo'), $result);

		// Exception thrown in worker
		$job->expects($this->any())->method('_run')->will($this->returnCallback(function (CM_Params $params) {
			throw new Exception();
		}));
		try {
			$job->run(array('foo' => 'bar'));
			$this->fail('Job should have thrown an exception');
		} catch (CM_Exception $ex) {
			$this->assertContains('Job `' . get_class($job) . '` failed', $ex->getMessage());
		}

		TH::clearConfig();
	}

	public function testRunGearmanDisabled() {
		CM_Config::get()->CM_Jobdistribution_Job_Abstract->gearmanEnabled = false;

		$job = $this->getMockForAbstractClass('CM_Jobdistribution_Job_Abstract', array(), '', true, true, true, array('_run'));
		$job->expects($this->exactly(2))->method('_run')->will($this->returnCallback(function (CM_Params $params) {
			return array_flip($params->getAllOriginal());
		}));

		/** @var CM_Jobdistribution_Job_Abstract $job */
		$result = $job->run(array('foo' => 'bar'));
		$this->assertSame(array('bar' => 'foo'), $result);

		$job->queue(array('foo' => 'bar'));

		TH::clearConfig();
	}

	public function testRunGearmanDisabledThrows() {
		CM_Config::get()->CM_Jobdistribution_Job_Abstract->gearmanEnabled = false;

		$job = $this->getMockForAbstractClass('CM_Jobdistribution_Job_Abstract', array(), '', true, true, true, array('_run'));
		$job->expects($this->exactly(1))->method('_run')->will($this->returnCallback(function (CM_Params $params) {
			throw new Exception('Job failed');
		}));

		/** @var CM_Jobdistribution_Job_Abstract $job */
		try {
			$job->run(array('foo' => 'bar'));
			$this->fail('Job should have thrown an exception');
		} catch (Exception $ex) {
			$this->assertSame('Job failed', $ex->getMessage());
		}

		TH::clearConfig();
	}

}
