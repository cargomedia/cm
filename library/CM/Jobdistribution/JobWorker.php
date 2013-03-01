<?php

class CM_Jobdistribution_JobWorker extends CM_Class_Abstract {

	/** @var GearmanWorker */
	private $_gearmanWorker;

	final public function __construct() {
		$worker = $this->_getGearmanWorker();
		$config = self::_getConfig();
		foreach ($config->servers as $server) {
			$worker->addServer($server['host'], $server['port']);
		}
		//use non-blocking IO mode to enable signal processing in worker processes as soon as libgearman/pecl gearman is fixed
		//see https://bugs.php.net/bug.php?id=60764
		//$this->_gearmanWorker->addOptions(GEARMAN_WORKER_NON_BLOCKING);
		$this->_registerJobs();
	}

	final public function run() {
		while (true) {
			$workFailed = false;
			try {
				$workFailed = !$this->_getGearmanWorker()->work();
			} catch (Exception $ex) {
				CM_Bootloader::handleException($ex);
			}
			if ($workFailed) {
				throw new CM_Exception_Invalid('Worker failed');
			}
		}
	}

	/**
	 * @return GearmanWorker
	 */
	protected function _getGearmanWorker() {
		if (!$this->_gearmanWorker) {
			$this->_gearmanWorker = new GearmanWorker();
		}
		return $this->_gearmanWorker;
	}

	final private function _registerJobs() {
		foreach (CM_Jobdistribution_Job_Abstract::getClassChildren() as $jobClassName) {
			$job = new $jobClassName();
			$this->_gearmanWorker->addFunction($jobClassName, array($job, '__run'));
		}
	}
}
