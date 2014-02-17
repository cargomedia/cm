<?php

class CM_Config_Mapping extends CM_Class_Abstract {

	/**
	 * @param $key
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	public function getConfigKey($key) {
		$mapping = $this->getMapping();
		if (!array_key_exists($key, $mapping)) {
			throw new CM_Exception_Invalid('There is no mapping for `' . $key . '`');
		}
		return $mapping[$key];
	}

	/**
	 * @return array
	 * @throws CM_Exception_Invalid
	 */
	final public function getMapping() {
		$mapping = $this->_getMapping();
		foreach (self::getClassChildren() as $childClass) {
			/** @var CM_Config_Mapping $child */
			$child = new $childClass();
			$mappingChild = $child->_getMapping();
			if ($duplicateKeys = array_intersect_key($mapping, $mappingChild)) {
				throw new CM_Exception_Invalid("Duplicate keys `" . implode(', ', $duplicateKeys) . '` found in `' . $childClass . '`');
			}
			$mapping = array_merge($mapping, $mappingChild);
		}
		return $mapping;
	}

	/**
	 * @return array
	 */
	protected function _getMapping() {
		return array(
			'Testhelper'   => 'CMTest_TH',
			'Render'       => 'CM_Render',
			'Mail'         => 'CM_Mail',
			'Tracking'     => 'CM_Tracking_Abstract',
			'Newrelic'     => 'CMService_Newrelic',
			'Redis'        => 'CM_Redis_Client',
			'SocketRedis'  => 'CM_Stream_Adapter_Message_SocketRedis',
			'Search'       => 'CM_Search',
			'Memcache'     => 'CM_Memcache_Client',
			'Database'     => 'CM_Db_Db',
			'Wowza'        => 'CM_Stream_Video',
			'JobManager'   => 'CM_Jobdistribution_JobManager',
			'JobWorker'    => 'CM_Jobdistribution_JobWorker',
			'Job'          => 'CM_Jobdistribution_Job_Abstract',
			'Splitest'     => 'CM_Model_Splittest',
			'Splitfeature' => 'CM_Model_Splitfeature'
		);
	}
}
