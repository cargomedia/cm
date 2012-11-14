<?php

abstract class CM_Job_Abstract extends CM_Class_Abstract {

	/** @var GearmanClient||null */
	private $_gearmanClient = null;

	/**
	 * @param CM_Params $params
	 * @return mixed
	 */
	abstract protected function _run(CM_Params $params);

	/**
	 * @param array $params
	 * @return mixed
	 */
	final public function run(array $params) {
		$params = CM_Params::factory($params);
		$config = $this->_getConfig();
		if (!$config->gearmanEnabled) {
			return $this->_run($params);
		}
		$workload = serialize($params);
		$this->_init();
		return $this->_gearmanClient->doNormal(get_class($this), $workload, $this->_getConfig()->timeout);
	}

	/**
	 * @param array $params
	 */
	final public function queue(array $params) {
		$params = CM_Params::factory($params);
		$config = $this->_getConfig();
		if (!$config->gearmanEnabled) {
			$this->_run($params);
			return;
		}
		$workload = serialize($params);
		$this->_init();
		$this->_gearmanClient->doBackground(get_class($this), $workload, $this->_getConfig()->timeout);
	}

	/**
	 * @param GearmanJob $job
	 * @return mixed
	 */
	final public function __run(GearmanJob $job) {
		$workload = $job->workload();
		$params = unserialize($workload);
		try {
			return $result = $this->_run($params);
		} catch (Exception $ex) {
			$job->sendException($ex->getMessage());
		}
	}

	final private function _init() {
		if (!$this->_gearmanClient) {
			$config = $this->_getConfig();
			$this->_gearmanClient = new GearmanClient();
			$servers = implode(',', array_map(function($server) {
				return implode(':', $server);
			}, $config->servers));
			$this->_gearmanClient->addServers($servers);
		}
	}

}