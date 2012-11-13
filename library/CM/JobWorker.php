<?php

final class CM_JobWorker extends CM_Class_Abstract {

	/** @var GearmanWorker */
	private $_gearmanWorker;

	public function __construct() {
		$this->_gearmanWorker = new GearmanWorker();
		$config = CM_Config::get()->CM_Gearman;
		$this->_gearmanWorker->addServer($config->server['host'], $config->server['port']);
		$this->_registerJobs();
	}

	public function run() {
		while ($this->_gearmanWorker->work()) {
			echo 'lol' . posix_getpid() . PHP_EOL;
			if ($this->_gearmanWorker->returnCode() == GEARMAN_SUCCESS) {
//				echo 'Worker ' . posix_getpid() . ' completed Job.' . PHP_EOL;
			} else {
//				echo 'Worker ' . posix_getpid() . ' returnCode: ' . $this->_gearmanWorker->returnCode() . PHP_EOL;
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