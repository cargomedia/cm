<?php

final class CM_JobWorker extends CM_Class_Abstract {

	/** @var GearmanWorker */
	private $_gearmanWorker;

	public function __construct() {
		$this->_gearmanWorker = new GearmanWorker();
		$config = $this->_getConfig();
		$servers = implode(',', array_map(function($server) {
			return implode(':', $server);
		}, $config->servers));
		$this->_gearmanWorker->addServers($servers);
		$this->_registerJobs();
//		$this->_gearmanWorker->addOptions(GEARMAN_WORKER_NON_BLOCKING);
	}

	public function run() {
		while ($this->_gearmanWorker->work()) {
			if ($this->_gearmanWorker->returnCode() == GEARMAN_SUCCESS) {
				echo 'Worker ' . posix_getpid() . ' completed Job.' . PHP_EOL;
			} else {
				echo 'Worker ' . posix_getpid() . ' returnCode: ' . $this->_gearmanWorker->returnCode() . PHP_EOL;
			}
		}
	}

	private function _registerJobs() {
		foreach (CM_Job_Abstract::getClassChildren() as $jobClassName) {
			$job = new $jobClassName();
			$this->_gearmanWorker->addFunction($jobClassName, array($job, '__run'));
			echo 'Registered: ' . $jobClassName . PHP_EOL;
		}
	}

}