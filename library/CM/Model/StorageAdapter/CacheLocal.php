<?php

class CM_Model_StorageAdapter_CacheLocal extends CM_Model_StorageAdapter_Cache {

	public function load(array $id) {
		return CM_CacheLocal::get($this->_getCacheKey($id));
	}

	public function save(array $id, array $data) {
		CM_CacheLocal::set($this->_getCacheKey($id), $data);
	}

	public function delete(array $id) {
		CM_CacheLocal::delete($this->_getCacheKey($id));
	}
}
