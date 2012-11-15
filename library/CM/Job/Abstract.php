<?php

abstract class CM_Job_Abstract extends CM_Class_Abstract {

	/** @var GearmanClient|null */
	private $_gearmanClient = null;

	/**
	 * @param CM_Params $params
	 * @return mixed
	 */
	abstract protected function _run(CM_Params $params);

	/**
	 * @param array $params
	 * @return string
	 */
	final public function run(array $params) {
		if ($this->_getGearmanEnabled()) {
			return $this->_dispatch($params);
		}
		return $this->_run(CM_Params::factory($params));
	}

	/**
	 * @param array $params
	 * @return string jobHandle
	 */
	final public function queue(array $params) {
		if ($this->_getGearmanEnabled()) {
			return $this->_dispatch($params, true);
		}
		return $this->_run(CM_Params::factory($params));
	}

	/**
	 * @param GearmanJob $job
	 * @return mixed
	 */
	final public function __run(GearmanJob $job) {
		$workload = $job->workload();
		$params = CM_Params::factory(CM_Params::decode($workload, true));
		try {
			return $this->_run($params);
		} catch (Exception $ex) {
			$job->sendFail();
			return null;
		}
	}

	/**
	 * @param array        $params
	 * @param boolean|null $asynchronous
	 * @throws CM_Exception
	 * @return string
	 */
	final private function _dispatch(array $params, $asynchronous = false) {
		$workload = CM_Params::encode($params, true);
		$gearmanClient = $this->_getGearmanClient();
		if ($asynchronous) {
			return $gearmanClient->doBackground(get_class($this), $workload, $this->_getConfig()->timeout);
		} else {
			$result = $gearmanClient->doNormal(get_class($this), $workload, $this->_getConfig()->timeout);
			if ($gearmanClient->returnCode() === GEARMAN_WORK_FAIL) {
				throw new CM_Exception('Job failed.');
			}
			return $result;
		}
	}

	/**
	 * @return boolean
	 */
	final private function _getGearmanEnabled() {
		return (boolean) self::_getConfig()->gearmanEnabled;
	}

	/**
	 * @return GearmanClient
	 */
	final private function _getGearmanClient() {
		if (!$this->_gearmanClient) {
			$config = static::_getConfig();
			$this->_gearmanClient = new GearmanClient();
			foreach ($config->servers as $server) {
				$this->_gearmanClient->addServer($server['host'], $server['port']);
			}
		}
		return $this->_gearmanClient;
	}

}