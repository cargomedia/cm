<?php

class CM_Model_StorageAdapter_Cache extends CM_Model_StorageAdapter_AbstractAdapter {

	/** @var string */
	private $_modelClass;

	/**
	 * @param string $modelClass
	 */
	public function __construct($modelClass) {
		$this->_modelClass = (string) $modelClass;
	}

	public function load(array $id) {
		return CM_Cache::get($this->_getCacheKey($id));
	}

	public function save(array $id, array $data) {
		CM_Cache::set($this->_getCacheKey($id), $data);
	}

	/**
	 * @param array $id
	 * @return string
	 */
	private function _getCacheKey(array $id) {
		return CM_CacheConst::Model . '_class:' . $this->_modelClass . '_id:' . serialize($id);
	}
}
