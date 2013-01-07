<?php

final class CM_Jobdistribution_JobWorker extends CM_Class_Abstract {

	/** @var GearmanWorker */
	private $_gearmanWorker;

	public function __construct() {
		$this->_gearmanWorker = new GearmanWorker();
		$config = self::_getConfig();
		foreach ($config->servers as $server) {
			$this->_gearmanWorker->addServer($server['host'], $server['port']);
		}
//		use non-blocking IO mode to enable signal processing in worker processes as soon as libgearman/pecl gearman is fixed
//		see https://bugs.php.net/bug.php?id=60764
//		$this->_gearmanWorker->addOptions(GEARMAN_WORKER_NON_BLOCKING);
		$this->_registerJobs();
	}

	public function run() {
		while ($this->_gearmanWorker->work()) {
		}
	}

	private function _registerJobs() {
		foreach (CM_Jobdistribution_Job_Abstract::getClassChildren() as $jobClassName) {
			$job = new $jobClassName();
			$this->_gearmanWorker->addFunction($jobClassName, array($job, '__run'));
		}
	}

}
