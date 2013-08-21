<?php

class CM_Model_StorageAdapter_CacheLocal extends CM_Model_StorageAdapter_Cache {

	public function load($type, array $id) {
		return CM_CacheLocal::get($this->_getCacheKey($type, $id));
	}

	public function save($type, array $id, array $data) {
		CM_CacheLocal::set($this->_getCacheKey($type, $id), $data);
	}

	public function delete($type, array $id) {
		CM_CacheLocal::delete($type, $this->_getCacheKey($type, $id));
	}
}
