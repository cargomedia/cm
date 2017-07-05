<?php

class CM_Model_StorageAdapter_CacheLocal extends CM_Model_StorageAdapter_Cache {

    public function load($type, array $id) {
        return CM_Cache_Local::getInstance()->get($this->_getCacheKey($type, $id));
    }

    public function save($type, array $id, array $data) {
        CM_Cache_Local::getInstance()->set($this->_getCacheKey($type, $id), $data);
    }

    public function delete($type, array $id) {
        CM_Cache_Local::getInstance()->delete($this->_getCacheKey($type, $id));
    }
}
