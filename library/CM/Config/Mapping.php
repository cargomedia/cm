<?php

class CM_Config_Mapping extends CM_Class_Abstract {

	/**
	 * @param $key
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	public function getConfigKey($key) {
		$mapping = $this->_getMapping();
		if (!array_key_exists($key, $mapping)) {
			throw new CM_Exception_Invalid('There is no mapping for `' . $key . '`');
		}
		return $mapping[$key];
	}

	/**
	 * @return array
	 */
	protected function _getMapping() {
		return array(
			'Testhelper'=> 'CMTest_TH',
			'Render' => 'CM_Render',
			'Mail' => 'CM_Mail',
			'Tracking' => 'CM_Tracking_Abstract',
			'Newrelic' => 'CMService_Newrelic',
			'Redis' => 'CM_Cache_Redis',
			'SocketRedis' => 'CM_Stream_Adapter_Message_SocketRedis',
			'Search' => 'CM_Search',
			'Memcache' => 'CM_Cache_Memcache',
			'Database' => 'CM_Db_Db',
			'Wowza' => 'CM_Stream_Video',
			'JobManager' => 'CM_Jobdistribution_JobManager',
			'JobWorker' => 'CM_Jobdistribution_JobWorker',
			'Job' => 'CM_Jobdistribution_Job_Abstract'
		);
	}

	/**
	 * @return CM_Config_Mapping
	 */
	public static function factory() {
		$className = self::_getClassName();
		return new $className();
	}

}
