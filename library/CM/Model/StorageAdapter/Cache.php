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

	public function create(array $data) {
		$this->save($this->_generateId(), $data);
	}

	public function delete(array $id) {
		CM_Cache::delete($this->_getCacheKey($id));
	}

	/**
	 * @param array $id
	 * @return string
	 */
	protected function _getCacheKey(array $id) {
		return CM_CacheConst::Model . '_class:' . $this->_modelClass . '_id:' . serialize($id);
	}

	/**
	 * @return array
	 */
	protected function _generateId() {
		throw new CM_Exception_NotImplemented();
	}
}
