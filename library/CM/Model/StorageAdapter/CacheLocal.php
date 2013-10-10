<?php

class CM_Model_StorageAdapter_CacheLocal extends CM_Model_StorageAdapter_Cache {

	public function load($type, array $id) {
		return CM_Cache_Local::getInstance()->get($this->_getCacheKey($type, $id));
	}

	public function loadMultiple(array $idTypeList) {
		$cacheEntryToArrayKey = array();
		foreach ($idTypeList as $key => $idType) {
			$cacheKey = $this->_getCacheKey($idType['type'], $idType['id']);
			$cacheEntryToArrayKey[$cacheKey] = $key;
		}
		$result = array();
		$values = CM_Cache_Local::getInstance()->getMulti(array_keys($cacheEntryToArrayKey));
		foreach ($values as $cacheKey => $value) {
			$key = $cacheEntryToArrayKey[$cacheKey];
			$result[$key] = $value;
		}
		return $result;
	}

	public function save($type, array $id, array $data) {
		CM_Cache_Local::getInstance()->set($this->_getCacheKey($type, $id), $data);
	}

	public function delete($type, array $id) {
		CM_Cache_Local::getInstance()->delete($type, $this->_getCacheKey($type, $id));
	}
}
