<?php

final class CM_JobWorker extends CM_Class_Abstract {

	/** @var GearmanWorker */
	private $_gearmanWorker;

	public function __construct() {
		$this->_gearmanWorker = new GearmanWorker();
		$config = self::_getConfig();
		foreach ($config->servers as $server) {
			$this->_gearmanWorker->addServer($server['host'], $server['port']);
		}
		$this->_registerJobs();
	}

	public function run() {
		while ($this->_gearmanWorker->work()) {
		}
	}

	private function _registerJobs() {
		foreach (CM_Job_Abstract::getClassChildren() as $jobClassName) {
			$job = new $jobClassName();
			$this->_gearmanWorker->addFunction($jobClassName, array($job, '__run'));
		}
	}

}